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


require_once 'TokenReflection/TokenParser.class.php';
require_once 'AOPConfigLoader.class.php';


class AOP {

	private static $configFile;
	private static $blacklist = array();
        private static $mainDir;

	public static function initializeAOP($configFile, $mainDir) {
		$Loader = new AOPConfigLoader();

		self::$configFile = $configFile;
                self::$mainDir = $mainDir;
		$Loader->doLoad(self::$configFile);
	}

	public static function addBlacklistEntry($entry) {
		self::$blacklist[] = $entry;
	}

	public static function aopRequire($class, $kind, $dir) {
		$prepare = 0;

		$origFile = $dir.DIRECTORY_SEPARATOR.$class.'.'.$kind.'.php';
		$origFileMtime = filemtime($origFile);
                
		//is file contained in black list?
		if(!empty(self::$blacklist)) {
			foreach(self::$blacklist as $blackListEntry) {
				$blackListEntry = preg_replace("/\//","\\/",$blackListEntry);
				if(preg_match("/".$blackListEntry."/", $origFile)) {
					require_once $origFile;
					return;
				}
			}
		}
		
		$cacheDirName = 'lib_aopcache'.DIRECTORY_SEPARATOR.str_replace(self::$mainDir,"",$dir);

		if(!is_dir($cacheDirName)) { mkdir_recursive(self::$mainDir . $cacheDirName, 0755); }

		$cacheFile = $cacheDirName.DIRECTORY_SEPARATOR.$class."_aopcache__.".$kind.".php";


		//cached file exists
		if(file_exists($cacheFile)) {
			$cachedFileMtime = filemtime($cacheFile);
			//but is older than class file
			if($cachedFileMtime < $origFileMtime) {
				$prepare = 1;
			}
			
			//config file newer than cached file
			if(file_exists(self::$configFile) && filemtime(self::$configFile) > $cachedFileMtime) {
				$prepare = 1;
			}
			//or cached file doesn't exists
		} else {
			$prepare = 1;
		}

		if($prepare == 1) {
			//all magic begins here...
			$Container = MainFactory::getAOPContainer();
			$Container->prepareAOPClass($origFile, self::$mainDir . $cacheFile);
		}

		require_once self::$mainDir . $cacheFile;
	}
}
