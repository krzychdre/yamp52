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



/** my own Classloader
 * It allows autoloading of *.class.php files and *.interface.php files
 * within include_path
 *
 */
function yampAutoloader($class) {
		$path = explode(":",get_include_path());
		if(!empty($path)) {
			$have = 0;
			foreach($path as $dir) {
				if(file_exists($dir.DIRECTORY_SEPARATOR.$class.'.class.php')) {
					$have = 'class';
					break;
				}
				if(file_exists($dir.DIRECTORY_SEPARATOR.$class.'.interface.php')) {
					$have = 'interface';
					break;
				}
			}
			if(!$have)
			return false;
		}

		AOP::aopRequire($class, $have, $dir);
		return true;
	}

spl_autoload_register('yampAutoloader');
//
