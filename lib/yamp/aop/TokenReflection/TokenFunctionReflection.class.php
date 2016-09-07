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


class TokenFunctionReflection {
	
	private $startTokenId; //id of first body token
	private $endTokenId; //id of last body token
	
	private $modifier;
	private $name;
	private $parameters;
	private $securityRequirements;
	private $isConstructor;

	private $body;
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function addToBody($str) {
		$this->body .= $str;
	}
	
	public function setModifier($modType) {
		$this->modifier = $modType;
	}
	
	/**
	 * array of strings with security definitions
	 * for further parsing
	 * 
	 * @param array $arr
	 */
	public function setSecurityReq($arr) {
		$this->securityRequirements = $arr;
	}
	
	public function getSecurityReq() {
		return $this->securityRequirements;
	}
	
	public function setAsConstructor() {
		$this->isConstructor = true;
	}
	
	public function setStartToken($id) {
		$this->startTokenId = $id;
	}
	public function getStartToken() {
		return $this->startTokenId;
	}
	
	public function setEndToken($id) {
		$this->endTokenId = $id;
	}
	public function getEndToken() {
		return $this->endTokenId;
	}
	
	public function addParameter($param) {
		$param = explode(",",$param);
		if(!empty($param)){ 
			foreach($param as $idx => $value) {
				$param[$idx] = trim($value);
			}
		}
		$this->parameters = $param;
	}
	
	public function getParameters() {
		return $this->parameters;
	}
}