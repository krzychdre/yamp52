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

session_start();
date_default_timezone_set('Europe/Warsaw');

//load extrass
require_once 'extrasFunctions.php';
require_once 'yamp/security/Roles.class.php';
require_once 'yamp/security/Acls.class.php';

$thisPath = realpath(dirname(__FILE__)."/../")."/";

addIncludePath(array(
	$thisPath.'lib',
	$thisPath.'lib/yamp',
	$thisPath.'lib/yamp/aop',
	$thisPath.'lib/yamp/IoC',
	$thisPath.'lib/yamp/security',
	$thisPath.'lib/vendor/smarty3/libs',
	$thisPath.'lib/doctrine-models',
	$thisPath.'lib/flow',
        $thisPath.'outbound/lib',
        $thisPath.'outbound/lib/doctrine-models'
        ));

require_once 'yamp/MainFactory.class.php';

//initialize AOP mechanism
require_once 'yamp/aop/AOP.class.php';
AOP::initializeAOP( $thisPath . 'conf/aopContainer.xml', $thisPath);

//load symfony dependency injection
require_once 'vendor/sfDependencyInjection/lib/sfServiceContainerAutoloader.php';
sfServiceContainerAutoloader::register();

//place our own ClassLoader in stack
require_once 'yamp/yampClassLoader.php';

$ServiceContainer = MainFactory::getServiceContainer();

//database init
$ServiceContainer->ConnectionFactory;

//initialize Smarty global variables container
$global = $ServiceContainer->GlobalVarContainer;

$Auth = $ServiceContainer->AuthenticationService;

$global->user = $Auth->getUserInfo();

$global->islogged = $Auth->isLogged();

$global->version = $ServiceContainer->Configuration->get("engine.version");
$global->enginename = $ServiceContainer->Configuration->get("engine.name");
