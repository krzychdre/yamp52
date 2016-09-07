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

class Fixture {

    /**
     * Mandatory method which will populate freshly created database
     * tables with objects (minimal configuration)
     *
     */
    public static function populate(\Doctrine\ORM\EntityManager $entityManager) {

        $entityManager->getConnection()->delete('Acl', array(1=>'true'));
        $entityManager->getConnection()->delete('Role', array(1=>'true'));
        $entityManager->getConnection()->delete('Role_Acl', array(1=>'true'));
        $entityManager->getConnection()->delete('Role_SecuredObject', array(1=>'true'));
        $entityManager->getConnection()->delete('Secured_Object', array(1=>'true'));
        $entityManager->getConnection()->delete('User', array(1=>'true'));
        $entityManager->getConnection()->delete('User_Acl', array(1=>'true'));
        $entityManager->getConnection()->delete('User_Role', array(1=>'true'));

        $Free = new Entity\Role();
        $Free->setName(Roles::$FREEACCESS);

        /**
         * everyone has access to these controllers and actions
         */

        $secur = array(
            array('MainFlowController', 'defaultAction'),
            array('AuthenticateFlowController', 'loginAction'),
            array('AuthenticateFlowController', 'dologinAction'),
            array('AuthenticateFlowController', 'dologinValidate'),
            array('AuthenticateFlowController', 'logoutAction'),
            array('AuthenticateFlowController', 'nopermAction')
        );

        foreach ($secur as $idx => $sec) {
            $Secured = new Entity\SecuredObject();
            $Secured->setClass($sec[0]);
            $Secured->setMethod($sec[1]);
            $Secured->setAllowed(true);
            $entityManager->persist($Secured);

            $Free->addSecuredObject($Secured);
        }

        $entityManager->persist($Free);
        /**
         * --------------------------------------------------------
         */



        $User = new Entity\Role();
        $User->setName(Roles::$USER);
        $entityManager->persist($User);

        $AdvancedUser = new Entity\Role();
        $AdvancedUser->setName(Roles::$ADVANCED_USER);
        $entityManager->persist($AdvancedUser);

        $Engineer = new Entity\Role();
        $Engineer->setName(Roles::$ENGINEER);
        $entityManager->persist($Engineer);

        $Admin = new Entity\Role();
        $Admin->setName(Roles::$ADMIN);
        $entityManager->persist($Admin);

        $Salesman = new Entity\Role();
        $Salesman->setName(Roles::$SALESMAN);
        $entityManager->persist($Salesman);

        $AclGlowna = new Entity\Acl();
        $AclGlowna->setName(Acls::$ACL_MAIN);
        $entityManager->persist($AclGlowna);

        $AclWazna = new Entity\Acl();
        $AclWazna->setName(Acls::$ACL_IMPORTANT);
        $entityManager->persist($AclWazna);

        $entityManager->flush();

        $Test = new Entity\User();
        $Test->setFirstName('Testoslav');
        $Test->setLastName('Tescinsky');
        $Test->setLogin('test'); //our user login
        $Test->setActive(true);
        $Test->setPassword( crypt('test') ); //and password

        $Test->addRole($Engineer);
        $Test->addRole($Admin);

        $Test->addAcl($AclGlowna);
        $Test->addAcl($AclWazna);

        $entityManager->persist($Test);

        $entityManager->flush();
        
        $secur = array(
            array('TestFlowController', 'actionValidate'),
            array('TestFlowController', 'actionAction'),
            array('TestFlowController', 'anotherAction'),
        );

        foreach ($secur as $idx => $sec) {
            $Secured = new Entity\SecuredObject();
            $Secured->setClass($sec[0]);
            $Secured->setMethod($sec[1]);
            $Secured->setAllowed(true);
            $entityManager->persist($Secured);

            $Admin->addSecuredObject($Secured);
        }

        $entityManager->persist($Admin);

        $sys = new Entity\System();
        $sys->setId(1);
        $sys->setInstalled(true);
        $entityManager->persist($sys);

        $entityManager->flush();
    }

}