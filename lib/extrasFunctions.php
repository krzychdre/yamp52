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




function l($array) {
	echo '<div style="position: absolute; z-index: 2000; background: #66cc99; '
	.'bottom: 30px; right: 30px; height: 40%; '
	.'width: 75%; padding:5px; overflow: auto; -moz-opacity: 0.88">';
	echo "<pre>";
	print_r($array);
	echo '</div>';
}


function addIncludePath($path) {
	$p = get_include_path();
	if(!empty($path)) {
		foreach($path as $idx => $row) {
			$p.=":".$row;
		}
	}
	set_include_path($p);
}

/**
 * Makes directory, returns TRUE if exists or made
 *
 * @param string $pathname The directory path.
 * @return boolean returns TRUE if exists or made or FALSE on failure.
 */

function mkdir_recursive($pathname, $mode) {
	is_dir(dirname($pathname)) || mkdir_recursive(dirname($pathname), $mode);
	return is_dir($pathname) || @mkdir($pathname, $mode);
}

function r_trim($array) {
	if(is_array($array) && !empty($array)) {
	foreach($array as $key => $value)
		if(is_array($value))
			$array[$key] = r_trim($value);
		else
			$array[$key] = trim($value);
	}
	return $array;
}
