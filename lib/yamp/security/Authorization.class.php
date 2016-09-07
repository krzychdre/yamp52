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

class Authorization {

    /**
     * @Service("Session")
     * @var Session
     */
    private $SessionContainer;
    /**
     * @Service("ConnectionFactory")
     * @var ConnectionFactory
     */
    private $connectionFactory;
    /**
     * @Service("AuthenticationService")
     * @var Authentication
     */
    private $AuthenticationService;

    public function getLoggedUser() {
        if ($this->AuthenticationService->isLogged()) {
            $User = $this->AuthenticationService->getUserInfo();
            return $User;
        }

        static $Anon;

        $Anon = Cache::get('Object_Anon');
        if (!is_object($Anon)) {
            $Anon = $this->connectionFactory->getEntityManager()->getRepository("Entity\Role")->findOneBy(array('name' => 'ANONYMOUS'));
            Cache::save('Object_Anon', $Anon);
        }

        $User = new Entity\User();
        $User->addRole($Anon);
        return $User;
    }

}