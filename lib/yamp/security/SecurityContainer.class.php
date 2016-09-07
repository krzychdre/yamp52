<?php

/*
 * 	Yamp52 - Yet Another Magical PHP framework
 * 	http://code.google.com/p/yamp52/
 * 	
 * 	Copyright (C) 2009, Krzysztof Drezewski <krzych@krzych.eu>
 * 	
 * 	This program is free software; you can redistribute it and/or modify
 * 	it under the terms of the GNU General Public License as published by
 * 	the Free Software Foundation; either version 3 of the License, or
 * 	(at your option) any later version.
 * 	
 * 	This program is distributed in the hope that it will be useful,
 * 	but WITHOUT ANY WARRANTY; without even the implied warranty of
 * 	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * 	GNU General Public License for more details.
 * 	
 * 	You should have received a copy of the GNU General Public License
 * 	along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Security Container is responsible for checking that user is allowed to call conroller/action
 * @package Yamp
 * @author krzych
 *
 */
class SecurityContainer {

    /**
     * @Service("AuthorizationService")
     * @var Authorization
     */
    private $AuthorizationService;
    /**
     * @Service("Session")
     * @var Session
     */
    private $SessionContainer;
    /**
     * @Service("ConnectionFactory")
     * @var ConnectionFactory
     */
    private $ConnectionFactory;

    /**
     *
     * @param string $class
     * @param string $method
     * @return boolean
     */
    public function isUserAllowedForAction($class, $method) {
        $User = $this->AuthorizationService->getLoggedUser();
        return $this->isAllowed($class, $method, $User);
    }

    private function transformToArrayOfNames($Roles) {
        if (($Roles instanceof Doctrine\Common\Collections\ArrayCollection ||
                $Roles instanceof Doctrine\ORM\PersistentCollection)
                && !$Roles->isEmpty()) {

            foreach ($Roles as $Role) {
                if (is_object($Role)) {
                    $roleNames[] = $Role->getName();
                }
            }
        }
        //every user has this role
        $roleNames[] = Roles::$FREEACCESS;
        return $roleNames;
    }

    /**
     *
     * @param string $class
     * @param string $method
     * @param User $User
     * @return boolean
     */
    public function isAllowed($class, $method, Entity\User $User) {
        static $arr;
        static $Roles;
        $result = false;

        if (!$class || !$method) {
            return false;
        }

        $Roles = $this->SessionContainer->getRaw('Roles');

        if (empty($Roles) || (is_object($Roles) && $Roles->isEmpty())) {
            $Roles = $User->getRoles();
            $this->SessionContainer->saveRaw('Roles', $Roles);
        }

        $roleNames = $this->transformToArrayOfNames($Roles);

        $result = $this->isRoleAllowed($class, $method, $roleNames);

        //default is not allowed
        return $result;
    }

    /**
     * Check if role names (given in array) has access to method of class from params
     * @param <type> $class
     * @param <type> $method
     * @param <type> $rolesArray - role names
     * @return boolean
     */
    private function isRoleAllowed($class, $method, $rolesArray) {
        $result = false;

        if (!empty($rolesArray)) {
            $roleNames = "'" . implode("','", $rolesArray) . "'";

            $queryBuilder = $this->ConnectionFactory->getEntityManager()->createQueryBuilder();
            $queryBuilder->select("so.allowed")
                    ->from("Entity\Role", "ro")
                    ->leftJoin("ro.securedObjects", "so")
                    ->where($queryBuilder->expr()->in("ro.name", $roleNames))
                    ->andWhere("so.class = :className")
                    ->andWhere("so.method = :methodName")
                    ->andWhere("so.allowed = true");

            $queryBuilder->setParameter("className", $class);
            $queryBuilder->setParameter("methodName", $method);
            $res = $queryBuilder->getQuery()->getArrayResult();
            if (!empty($res)) {
                foreach ($res as $idx => $row) {
                    if ($row['allowed'] == 1) {
                        $result = true;
                        break;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * check if user or his role has given acl
     *
     * ACLs are not inherited, that means ancestors acls doesn't count.
     * @return boolean
     */
    public function hasAcl($aclName) {
        $result = false;
        $acls = $this->SessionContainer->getRaw('UserAcl');
        if (!$acls) {
            $User = $this->AuthorizationService->getLoggedUser();
            $acls = $this->fetchUserAcls($User);

            $this->SessionContainer->saveRaw('UserAcl', $acls);
        }
        if (!empty($acls)) {
            $key = array_search($aclName, $acls);
            if ($key !== false) {
                $result = true;
            }
        }
        return $result;
    }

    /**
     * returns flat array from results given by
     * fetchRoleAcl and fetchUserAcl
     *
     * @param array $result
     * @return array
     */
    private function flattenAclResult($result) {
        $ret = array();
        if (!empty($result)) {
            foreach ($result as $idx => $row) {
                $ret[] = $row['name'];
            }
        }
        return $ret;
    }

    private function fetchRoleAcl($Role) {
        $result = array();
        if ($Role != null) {
            $queryBuilder = $this->ConnectionFactory->getEntityManager()->createQueryBuilder();
            $queryBuilder->select("a.id,a.name,r.id")
                    ->from("Entity\Role", "r")
                    ->leftJoin("r.acls", "a")
                    ->where("r.id = :roleId")
                    ->setParameter("roleId", 1);
            $result = $queryBuilder->getQuery()->getArrayResult();
        }
        return $this->flattenAclResult($result);
    }

    /**
     * returns user's acls
     * @param User $User
     * @return array
     */
    private function fetchUserAcls(Entity\User $User) {
        $queryBuilder = $this->ConnectionFactory->getEntityManager()->createQueryBuilder();
        $queryBuilder->select("a.id,a.name,u.id")
                ->from("Entity\User", "u")
                ->leftJoin("u.acls", "a")
                ->where("u.id = :userId")
                ->setParameter("userId", $User->getId());
        $result = $queryBuilder->getQuery()->getArrayResult();
        $acls = $this->flattenAclResult($result);

        foreach ($User->getRoles() as $Role) {
            $acls = array_merge($this->fetchRoleAcl($Role), $acls);
        }
        return $acls;
    }

    /**
     * checks if user has given role
     *
     * Roles are inherited, that means user has all ancestor's roles
     *
     * @return boolean
     */
    public function hasRole($roleName) {
        static $RolesArray;
        $key = false;
        $result = false;

        $RolesArray = $this->SessionContainer->getRaw('RolesArray');
        if (empty($RolesArray)) {
            //we haven't array of user roles yet
            $Roles = $this->AuthorizationService->getLoggedUser()->getRoles();
            $RolesArray = $this->transformToArrayOfNames($Roles);
        }
        if (!empty($RolesArray)) {
            $key = array_search($roleName, $RolesArray);
            if ($key === false) {
                $result = false;
            } else {
                $result = true;
            }
        }
        return $result;
    }

}