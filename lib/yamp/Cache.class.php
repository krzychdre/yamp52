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

class Cache {

    public static function save($variable, $value) {

        if (self::checkForApc()) {
            apc_add($variable, $value);
        }
    }

    public static function is_set($variable) {
        if (self::checkForApc() && self::get($variable)) {
            return true;
        }
        return false;
    }

    public static function get($variable) {
        if (self::checkForApc()) {
            return apc_fetch($variable);
        }
    }

    public static function remove($variable) {
        if (self::checkForApc()) {
            return apc_delete($variable);
        }
    }

    public static function clear() {
        if (self::checkForApc()) {
            return apc_clear_cache();
        }
    }

    private static function checkForApc() {
        if (function_exists('apc_fetch')) {
            return true;
        }
        return false;
    }

}