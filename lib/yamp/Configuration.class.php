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

class Configuration {

    private $files;
    private $mainPath;
    private $config;

    public function __construct($mainPath, $files) {

        $this->mainPath = $mainPath.DIRECTORY_SEPARATOR;
        $this->config['www.dir'] = $this->mainPath;

        $this->files = $files;
        if (is_array($files)) {
            foreach ($files as $file) {
                if (file_exists($this->mainPath.$file)) {
                    $this->config = array_merge($this->config, parse_ini_file($this->mainPath.$file, false));
                } else {
                    throw new Exception("File " . $file . " does not exists.");
                    die;
                }
            }
        } else {
            throw new IllegalArgumentException("I need an array argument");
            die;
        }
    }

    public function get($name) {
        if (array_key_exists($name, $this->config) === false) {
            throw new IllegalArgumentException("Invalid configuration key: " . $prefix . ":" . $name);
        }
        return $this->config[$name];
    }

}