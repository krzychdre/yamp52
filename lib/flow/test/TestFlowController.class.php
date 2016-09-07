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


class TestFlowController {

	/**
	 * @var DispatcherController
	 */
	protected $Dispatcher;
	
	/**
	 * @var ErrorHandler
	 */
	protected $ErrorHandler;

	/**
	 * 
	 * @var Smarty
	 */
	protected $Smarty;

	
	/**
	 * Example of function that 
	 * @return unknown_type
	 */
	public function actionAction() {
		$this->Smarty->assign('action','actionAction');
		$this->Smarty->display('myview.html');
	}

	/**
	 * Example of function that validates $_POST['test'] array
	 * @param $postParams
	 * @return unknown_type
	 */
	public function actionValidate($postParams) {
		//action "actionAction" doesn't like empty postParams so, get out!
		if(empty($postParams)) { 
			return false;
		}
		
		$test = $postParams['test'];

		if($test['sometext'] == "") {
			$this->ErrorHandler->addError('sometext',new Message(MESSAGE_ERR, 'This field is mandatory'));
		}

		return !$this->ErrorHandler->hasErrors();
	}


	/**
	 * Another example action
	 * @return unknown_type
	 */
	public function   anotherAction($jeden=null, $dwa=null) {
		$this->Smarty->assign('action','anotherAction');
		$this->Smarty->display('myview.html');
	}

	
	/**
	 * @Service("ErrorHandler")
	 */
	public function setErrorHandler($errorHandler) {
		$this->ErrorHandler = $errorHandler;
	}
	
	/**
	 * @Service("dispatcher")
	 */
	public function setDispatcher($dispatcher) {
		$this->Dispatcher = $dispatcher;
	}
	
	/**
	 * @Service("smarty")
	 */
	public function setSmarty($smarty) {
		$this->Smarty = $smarty;
	}
	
}