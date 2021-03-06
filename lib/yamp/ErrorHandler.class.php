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


class ErrorHandler {

	/**
	 * @Service("Session")
	 * @var Session
	 */
	private $SessionContainer;
	
	private $errors = array();
	
	public function addError($param, $error) {
		$this->errors[$param] = $error;
		
		$err = $this->SessionContainer->get('errors');
		$err[$param] = $error;
		$this->SessionContainer->save('errors', $err);
	}
	
	public function retrieveErrors() {
		$errors = $this->SessionContainer->get('errors');
		$this->SessionContainer->remove('errors');
		return $errors;
	}
	
	/**
	 * 
	 * @return boolean
	 */
	public function hasErrors() {
		return (boolean)sizeof($this->errors);
	}
	
}