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
 * Session Cache Driver
 *
 * @package     Yamp
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @author      krzych
 */
class Doctrine_Cache_Session extends Doctrine_Cache_Driver
{
	private static $scope = 'session_cache';

	/**
	 * 
	 * @var Session
	 */
	private $SessionContainer;

	/**
	 * constructor
	 *
	 * @param array $options    associative array of cache driver options
	 */
	public function __construct($options = array())
	{
		$this->SessionContainer = MainFactory::getServiceContainer()->Session;
		//$this->SessionContainer->clearRaw(self::$scope);
		parent::__construct($options);
	}

    /**
     * Fetch a cache record from this cache driver instance
     *
     * @param string $id cache id
     * @param boolean $testCacheValidity        if set to false, the cache validity won't be tested
     * @return string cached datas (or false)
     */
    protected function _doFetch($id, $testCacheValidity = true)
	{
		$results = $this->SessionContainer->getRaw($this->_getKey($id),self::$scope, false);
		if(!empty($results)) {
			return $results;
		} 
		return false;
	}

    /**
     * Test if a cache record exists for the passed id
     *
     * @param string $id cache id
     * @return mixed false (a cache is not available) or "last modified" timestamp (int) of the available cache record
     */
    protected function _doContains($id) 
	{
		$data = $this->SessionContainer->getRaw($this->_getKey($id), self::$scope, false);
		if(!empty($data) ) {
			return true;
		}
		return false;
	}

    /**
     * Save a cache record directly. This method is implemented by the cache
     * drivers and used in Doctrine_Cache_Driver::save()
     *
     * @param string $id        cache id
     * @param string $data      data to cache
     * @param int $lifeTime     if != false, set a specific lifetime for this cache record (null => infinite lifeTime)
     * @return boolean true if no problem
     */
    protected function _doSave($id, $data, $lifeTime = false)
	{
		$this->SessionContainer->saveRaw($this->_getKey($id), $data, self::$scope, false);
		return true;
	}

    /**
     * Remove a cache record directly. This method is implemented by the cache
     * drivers and used in Doctrine_Cache_Driver::delete()
     * 
     * @param string $id cache id
     * @return boolean true if no problem
     */
    protected function _doDelete($id)
	{
		return $this->SessionContainer->removeRaw($this->_getKey($id), self::$scope);
	}
	
}
