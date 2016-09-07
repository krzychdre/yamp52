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
 * An universal storage object for Smarty global $layout variable
 * 
 * 
 * @package Yamp
 * @author krzych
 *
 */
class SmartyGlobalVarContainer {
	
	/**
	 * @var Smarty
	 */
	private $Smarty;
	
	private $variable = 'layout';
	
	/**
	 * This object's content var
	 * @var array
	 */
	private $content;

	/**
	 * Returns any variable from this object's content
	 * 
	 * @param string $var
	 * @return mixed
	 */
	public function __get($var) {
		return $this->content[$var];
	}
	
	/**
	 * Sets given value to this object's content array into $var key
	 * 
	 * @param string $var
	 * @param mixed $value
	 */
	public function __set($var, $value) {
		$this->content[$var] = $value;
		$this->Smarty->assign($this->variable, $this->content);
	}
	
	/**
	 * Used for smarty injection.
	 * 
	 * This is mandatory in this case because __set and __get methods are already used
	 * for accessing content variable
	 * 
	 * @Service("smarty")
	 */
	public function setSmarty($Smarty) {
		$this->Smarty = $Smarty;
	}
}