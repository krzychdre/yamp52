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


/**
 * Web-based session 
 * 
 * @package Yamp
 * @author krzych
 *
 */
class Session {

	private $sessid;
	
	private $SID;

	private $content;
	
	public function __construct($sessid) {
		$this->sessid = $sessid;
		$this->restoreSession();
	}

	/**
	 * Destroys web session
	 * 
	 * Unsets content variable, cookie
	 * and destroys web session
	 */
	public function destroySession() {
		$this->SID = null;
		setcookie($this->sessid."_SID",$this->SID);
		
		$this->content = array();
		unset($_SESSION);
		session_destroy();
	}
	
	/**
	 * Returns variable from Session's content var
	 * 
	 * @param unknown_type $paramName
	 * @return unknown_type
	 */
	public function get($paramName) {
		return unserialize($this->content[$paramName]);
	}

	/**
	 * Returns variable from $_SESSION superglobal
	 * 
	 * Use it with care. There are save/get methods that are better suited for your
	 * needs.
	 * @param string $paramName
	 * @return mixed
	 */
	public function getRaw($paramName, $scope = 'raw', $serialize = true) {
		$data = $_SESSION[$scope][$paramName];
		if($serialize === true) {
			return unserialize($data);
		} else {
			return $data;
		}
	}
	
	/**
	 * Saves variable into Session
	 * 
	 * @param string $paramName
	 * @param mixed $value
	 */
	public function save($paramName, $value) {
		$this->content[$paramName] =  serialize($value);
		$this->saveSession();
	}
	
	/**
	 * Saves variable directly to a $_SESSION superglobal
	 * 
	 * Use it with care. There are save/get methods that are better suited for your
	 * needs.
	 * 
	 * @param string $paramName
	 * @param mixed $value
	 */
	public function saveRaw($paramName, $value, $scope = 'raw', $serialize = true) {
		if($serialize === true) {
			$value = serialize($value);
		}
		$_SESSION[$scope][$paramName] = $value;
	}
	
	/**
	 * removes any content in "raw" scope.
	 * 
	 */
	public function clearRaw($scope = 'raw') {
		unset($_SESSION[$scope]);
	}
	

	/**
	 * Removes any content from Session's content var hidden under given key
	 * @param string $paramName
	 */
	public function remove($paramName) {
		unset($this->content[$paramName]);
		$this->saveSession();
	}
	
	public function removeRaw($paramName, $scope = 'raw') {
		unset($_SESSION[$scope][$paramName]);
	}

	/**
	 * Load and deserialize an Object from Session
	 * 
	 * @param string $variable
	 * @throws Exception when there is no object to load
	 * @return Object
	 */
	public function getObject($variable) {
		if(isset($this->content[$variable]) && is_object($this->get($variable))) {
			return $this->get($variable);
		} else {
			throw new Exception("Tried to load an Object from ".__CLASS__." but got NULL");
		}
	}
	

	/**
	 * returns object stored in 'raw' scope of session
	 * 
	 * @param string $variable
	 * @return Object
	 */
	public function getRawObject($variable, $scope = 'raw', $serialize = true) {
		$obj = $this->getRaw($variable, $scope, $serialize);
		if(is_object($obj)) {
			return $obj;
		} else {
			throw new Exception("Tried to load an Object from ".__CLASS__." but got NULL");
		}
	}

	/**
	 * Checks that variable given in parameter is set in session's contents
	 * 
	 * @param string $paramName
	 * @return boolean
	 */
	public function is_set($paramName) {
		if(isset($this->content[$paramName])) {
			return true;
		}
	}
	
//---------------------------------------- P R I V A T E -----------------------------------------

	/**
	 * Generates unique session id
	 * @return string
	 */
	private function makeSID() {
		list($usec, $sec) = split(' ', microtime());
		return md5(uniqid(rand(), true)).sprintf('%09x', $sec).sprintf('%07x', ($usec * 10000000));
	}

	/**
	 * Tries to restore session by testing cookie or
	 * creates a new one
	 */
	private function restoreSession() {
		if(isset($_COOKIE[$this->sessid."_SID"])) {
			$this->content = $_SESSION['content'];
		} else {
			$this->createSession();
		}
	}

	/**
	 * Saves content of the Session object to $_SESSION superglobal var
	 */
	private function saveSession() {
		$_SESSION['content'] = $this->content;
	}

	/**
	 * Creates new web based session
	 */
	private function createSession() {
		$this->SID = $this->makeSID();
		$this->content = array();
		$this->saveSession();
		setcookie($this->sessid."_SID", $this->SID);
	}
	
}