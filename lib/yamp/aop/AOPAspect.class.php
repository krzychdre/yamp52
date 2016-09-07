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


class AOPAspect {
	
	private $pattern;
	private $when;
	private $classToCall;
	private $methodToCall;
	private $methodParams;
	
	
	public function __construct($pattern, $when, $classToCall, $methodToCall, $methodParams) {
		if(!$pattern || !$methodToCall || !$when) {
			throw new InvalidArgumentException("Insufficient parameters to construct AOPAspect");
		}
		$this->pattern = $pattern;
		$this->when = $when;
		$this->classToCall = $classToCall;
		$this->methodToCall = $methodToCall;
		$this->methodParams = $methodParams;
	}
	
	public function getPattern() {
		return $this->pattern;
	}
	
	public function getWhen() {
		return $this->when;
	}
	
	public function getClassToCall() {
		return $this->classToCall;
	}
	
	public function getMethodToCall() {
		return $this->methodToCall;
	}
	
	public function getMethodParams() {
		return $this->methodParams;
	}
	
	/**
	 * md4 hash for unique checking
	 * @return unknown_type
	 */
	public function getHash() {
		return hash('md4',$this->pattern.$this->when.$this->classToCall.$this->methodToCall.$this->methodParams);
	}
	
	/**
	 * return complete string for injection of AOP method in * class
	 * @param $function
	 * @return unknown_type
	 */
	public function getCallToInject($function) {
		$functionParams = $function->getParameters();
		if(!empty($functionParams)) {
			foreach($functionParams as $idf => $param) {
				$pm = explode("=",$param);
				$functionParams[$idf] = $pm[0];
			}
		}
		$str = '';
		
		$str .= "/** AOP - ".__CLASS__." */\n";
		if($this->classToCall) {
			$class = $this->classToCall;
			$str .= 'MainFactory::getServiceContainer()->'.$class.'->';
		}
		$str .= $this->methodToCall.'(';

		//mapping parameters - any $_x means x-th parameter of target method
		if(!empty($this->methodParams)) {
			$params = explode(",",$this->methodParams);
			$tmpParameter = array();
			foreach($params as $idp => $param) {
				$param = trim($param);
				if(preg_match('/\$_[0-9]+/',$param) && !empty($functionParams)) {
					$fp = $functionParams[preg_replace('/\$_/',"", $param)-1];
					if($fp) { 
						$tmpParameter[] = $fp; 
					} 
					
				} elseif(!empty($functionParams)) {
					if($param) { 
						$tmpParameter[] = $param;	
					}
				}
			}
			$str .= implode(", ", $tmpParameter);
		}

		$str .= ');';
		return $str;
	}
}