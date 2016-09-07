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

/**
 * Bootstrap class for preconfigure Smarty
 * 
 * @package Yamp
 * @return Smarty
 */
class SmartyConfig extends Smarty {

    public function __construct($plugins_dir, $templates_dir, $compile_dir, $force_compile=false) {
        parent::__construct();
        $this->compile_dir = $compile_dir;
        $this->plugins_dir = $plugins_dir;
        $this->templates_dir = $templates_dir;
        $this->force_compile = $force_compile;
    }

}