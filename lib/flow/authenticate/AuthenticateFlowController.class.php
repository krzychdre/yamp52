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

class AuthenticateFlowController {

    /**
     * @Service("smarty")
     * @var Smarty
     */
    private $Smarty;
    /**
     * @Service("Configuration")
     * @var Configuration
     */
    private $Configuration;
    /**
     * @Service("dispatcher")
     * @var DispatcherController
     */
    private $DispatcherController;
    /**
     * @Service("ErrorHandler")
     * @var ErrorHandler
     */
    private $ErrorHandler;
    /**
     * @Service("GlobalVarContainer")
     * @var SmartyGlobalVarContainer
     */
    private $Layout;
    /**
     * @Service("Session")
     * @var unknown_type
     */
    private $SessionContainer;
    /**
     * @Service("AuthenticationService")
     * @var Authentication
     */
    private $AuthenticationService;


    //---------------------------------------------------------------------------------------------------------

    /**
     * Display login form <- entry point of this controller
     *
     */
    public function loginAction() {
        $this->Layout->pagetitle = 'Login screen';
        $this->Layout->viewtitle = 'Please log in';

        $params = $this->DispatcherController->getPostParams();
        $this->Smarty->assign('login', $params['login']);
        $this->Smarty->display('authenticate/login.html');
    }

    /**
     * If doLoginValidate passed true then step in this method
     */
    public function doLoginAction() {
        $this->AuthenticationService->doLogin();
        header('Location: /' . $this->Configuration->get("www.uri"));
        die;
    }

    /**
     * Validate if every login form parameter is ok
     * @return boolean
     */
    public function doLoginValidate($postParams) {
        if (empty($postParams)) {
            return false;
        }

        $login = $postParams['login'];

        if (!$login['login']) {
            $this->ErrorHandler->addError('login', new Message(MESSAGE_ERR, 'Login field is mandatory'));
        }
        if (!$login['password']) {
            $this->ErrorHandler->addError('password', new Message(MESSAGE_ERR, 'Password field is mandatory'));
        } else {

            $Msg = $this->AuthenticationService->checkForUser($login['login'], $login['password']);
            if ($Msg->getStatus() == MESSAGE_ERR) {
                $this->ErrorHandler->addError('password', $Msg);
            }
        }
        return !$this->ErrorHandler->hasErrors();
    }

    /**
     * this is called when user hasn't permissions to controller/action
     * @return unknown_type
     */
    public function nopermAction() {
        $msg_ident = 'noperm_message';

        $this->Layout->pagetitle = 'Insufficient permissions.';
        $this->Layout->viewtitle = '';

        if ($this->SessionContainer->is_set($msg_ident)) {
            $this->Smarty->assign('message',
                    $this->SessionContainer->getObject($msg_ident));
            $this->SessionContainer->remove($msg_ident);
        }

        $this->Smarty->display('authenticate/noperm.html');
    }

    /**
     * logout method without validation
     */
    public function logoutAction() {
        $this->AuthenticationService->doLogout();
        header('Location: /' . $this->Configuration->get("www.uri"));
        die;
    }

}