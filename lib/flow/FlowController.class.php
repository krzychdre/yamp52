<?php
/*
 * 	Yamp52 - Yet Another Magical PHP framework
 *	http://code.google.com/p/yamp52/
 *	
 *	Copyright (C) 2009, Krzysztof Drezewski <krzych@krzych.eu>
 *	
 *	This program is free software; you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation; either version 3 of the License, or
 *	(at your option) any later version.
 *	
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 *	GNU General Public License for more details.
 *	
 *	You should have received a copy of the GNU General Public License
 *	along with this program. If not, see <http://www.gnu.org/licenses/>.
 */


class FlowController {

	/**
	 * @Service("smarty")
	 * @var Smarty
	 */
	private $Smarty;
	
	/**
	 * @Service("ErrorHandler")
	 * @var ErrorHandler
	 */
	private $ErrorHandler;

	private $Controller;
	private $ref;
	
	/**
	 * @Service("SecurityContainer")
	 * @var SecurityContainer
	 */
	private $SecurityContainer;

	public function registerController($Controller) {
		$this->checkForController($Controller);
		$this->Controller = $Controller;
		$this->ref = new ReflectionClass($this->Controller);
	}

	public function callAction($action) {
		$this->checkForController();
		
		$this->checkForPermissions($action);
				
		if($this->ref->hasMethod($action)) {
			try {
				$this->Controller->$action();
			} catch (Exception $e) {
				DispatcherController::exceptionEnvelope($e);
			}
		} else {
			$e = new Exception("Controller ".$this->ref->getName()." has't action named ".$action);
			DispatcherController::exceptionEnvelope($e);
		}
	}

	public function validateAction($action, $postParams) {
		$this->checkForController();
		
		if($this->ref->hasMethod($action)) {
			$this->checkForPermissions($action);
			return $this->Controller->$action($postParams);
		}
		return true;
	}

	public function retreiveValidationErrors() {
		$errors = $this->ErrorHandler->retrieveErrors();
		$this->Smarty->assign('error', $errors);
	}

	private function checkForController($Controller=null) {
		$controller = ($Controller)?$Controller : $this->ref;
		if(!is_object($controller)) {
			$e = new Exception("I have no instance of Controller");
			DispatcherController::exceptionEnvelope($e);
		}
	}
	
	private function checkForPermissions($action) {
		$result = $this->SecurityContainer->isUserAllowedForAction($this->ref->getName(), $action);
		if($result !== true) {
			$e = new ActionInsufficientPermissionsException("Insufficient permisions to call ".$this->ref->getName()."->".$action);
			DispatcherController::exceptionEnvelope($e);
		}
	}


}