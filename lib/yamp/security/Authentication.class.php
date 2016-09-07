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

class Authentication {

    /**
     * @Service("Session")
     * @var Session
     */
    private $SessionContainer;
    /**
     * @var User
     */
    private $user;
    /**
     * @Service("Configuration")
     * @var Configuration
     */
    private $Configuration;
    /**
     * @Service("ConnectionFactory")
     * @var ConnectionFactory
     */
    private $ConnectionFactory;
    /**
     * @Service("GlobalVarContainer")
     * @var SmartyGlobalVarContainer
     */
    private $Layout;

    /**
     * Perform user authentication check
     * @param string $userlogin
     * @param string $password
     * @return Message|Message|Message|Message
     */
    public function checkForUser($userlogin, $password) {
        $result = new Message(MESSAGE_OK);

        $this->user = $this->ConnectionFactory->getEntityManager()->getRepository("Entity\User")
                        ->findOneBy(array("login" => $userlogin));

        if (!is_object($this->user)) {
            $result =  new Message(MESSAGE_ERR, "Unknown user");
        } elseif ($this->user->isActive() === false) {
            $result =  new Message(MESSAGE_ERR, "Account has been blocked");
        } elseif (crypt($password, $this->user->getPassword ()) != $this->user->getPassword()) {
            $result =  new Message(MESSAGE_ERR, "Incorrect login or password");
        }
        return $result;
    }

    public function doLogin() {
        $this->isLogged = true;

        //clear any cache info that resides in session
        
        $this->SessionContainer->clearRaw();
        $this->SessionContainer->saveRaw('authenticated_user', $this->user->getId());
    }

    public function doLogout() {
        $this->isLogged = false;
        $this->user = array();
        $this->SessionContainer->destroySession();
    }

    /**
     * @return boolean
     */
    public function isLogged() {

        $info = $this->getUserInfo();
        if ($info instanceof Entity\User) {
            $this->user = $info;
            return true;
        }
        return false;
    }

    /**
     * returns info about logged user
     *
     * @return Doctrine_Record
     */
    public function getUserInfo() {
        static $info; //anyway - cache internally and in the session

        if (!$info) {
            try {
                $info = $this->SessionContainer->getRawObject('UserInfo');
            } catch (Exception $e) {
                $id = $this->SessionContainer->getRaw('authenticated_user');
                if ($id) {
                    $query = $this->ConnectionFactory->getEntityManager()->
                        createQuery("SELECT u,ro,ac FROM Entity\User u
                                        LEFT JOIN u.roles ro
                                        LEFT JOIN u.acls ac
                                        WHERE u.id = :userId")->
                        setParameter("userId", ($id) ? $id : 0)->
                        useResultCache(true);
                    $info = $query->getSingleResult();
                    $this->SessionContainer->saveRaw('UserInfo', $info);
                }
            }
        }
        return $info;
    }

}
