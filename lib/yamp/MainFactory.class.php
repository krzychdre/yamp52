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
 * Main containers factory
 *
 * @package Yamp
 * @author krzych
 *
 */
class MainFactory {

	private static $instanceServiceContainer = null;
	private static $instanceAOPContainer = null;

	/**
	 * returns instance of extended symfony ServiceContainer
	 *
	 * This container supports annotation based dependency injection
	 *
	 * @return YampServiceContainerBuilder
	 */
	public static function getServiceContainer() {
		if (!is_object(self::$instanceServiceContainer)) {
			//init our services injection via annotations
			$sc = new YampServiceContainerBuilder();

			$sc->addAnnotationListener(new ServiceInjectionListener());

			$loader = new sfServiceContainerLoaderFileXml($sc);
			$loader->load(dirname(__FILE__).'/../../conf/baseContainer.xml');
			self::$instanceServiceContainer = $sc;
		}
		return self::$instanceServiceContainer;
	}

	/**
	 * returns AOPContainer
	 *
	 * @return AOPContainer
	 */
	public static function getAOPContainer() {
		if (!is_object(self::$instanceAOPContainer)) {
			//init our services injection via annotations
			require_once 'aop/AOPContainer.class.php';
			$aop = new AOPContainer();

			//add security support to AOP
			require_once 'aop/SecurityAOPListener.interface.php';
			require_once 'security/TokenSecurityAOPListener.class.php';
			$SecurityAOPListener = new TokenSecurityAOPListener();
			$aop->setSecurityListener($SecurityAOPListener);
				
			self::$instanceAOPContainer = $aop;
		}
		return self::$instanceAOPContainer;
	}

}
