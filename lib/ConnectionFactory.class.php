<?php

use Doctrine\Common\ClassLoader, Doctrine\ORM\EntityManager;

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

class ConnectionFactory {

    /**
     * @var Configuration
     */
    private $Conf;
    private static $entityManager;

    public function __construct() {

        //injection will not work when you call method from constructor :(
        $this->Conf = MainFactory::getServiceContainer()->Configuration;


        require_once 'Doctrine/Common/ClassLoader.php';

        $classLoader = new \Doctrine\Common\ClassLoader('Doctrine');
        $classLoader->register();

        $classLoader = new \Doctrine\Common\ClassLoader('DoctrineExtensions', $this->Conf->get("www.dir") . '/lib/vendor');
        $classLoader->register();

        $classLoader = new \Doctrine\Common\ClassLoader('Entity', $this->Conf->get("www.dir") . '/lib/Mapping');
        $classLoader->register();

        $classLoader = new \Doctrine\Common\ClassLoader('Proxy', $this->Conf->get("www.dir") . '/lib/Mapping');
        $classLoader->register();

        $config = new Doctrine\ORM\Configuration();

        $config->setProxyDir($this->Conf->get("www.dir") . '/lib/Proxies');
        $config->setProxyNamespace($this->Conf->get("engine.name") . '\Proxies');
        $config->setAutoGenerateProxyClasses(($this->Conf->get("www.production") == "development"));

        $driverImpl = $config->newDefaultAnnotationDriver($this->Conf->get("www.dir") . '/lib/Mapping');
        $config->setMetadataDriverImpl($driverImpl);

        if ($this->Conf->get("www.production") == "development") {
            $cache = new \Doctrine\Common\Cache\ArrayCache();
        } else {
            $cache = new \Doctrine\Common\Cache\ApcCache();
        }
        $config->setMetadataCacheImpl($cache);
        $config->setQueryCacheImpl($cache);

        // obtaining the entity manager
        $evm = new Doctrine\Common\EventManager();
        self::$entityManager = \Doctrine\ORM\EntityManager::create($this->getConnectionParams(), $config, $evm);

        /**
         * in development stage we are checking if database connection is ok
         * every time

         */
        if ($this->Conf->get("www.production") == "development") {
            $SystemInfo = self::$entityManager->getRepository("Entity\System")->findOneBy(array('isInstalled' => 1));
            if (!is_object($SystemInfo)) {
                try {
                    Fixture::populate($this->entityManager);
                    die('Please reload after entities creation');
                } catch (Exception $e) {
                    die("Something went wrong with populating the database" . $e->getMessage());
                }
            }
        }
    }

    /**
     *
     * @return Doctrine\ORM\EntityManager
     */
    public function getEntityManager() {
        return self::$entityManager;
    }
    
    /**
     * Translate from ini parameters to pdo
     * @return array of connection params for PDO
     */
    private function getConnectionParams() {
        if ($this->Conf->get("db.name")) {
            $conParam['dbname'] = $this->Conf->get("db.name");
        }
        if ($this->Conf->get("db.username")) {
            $conParam['user'] = $this->Conf->get("db.username");
        }
        if ($this->Conf->get("db.password")) {
            $conParam['password'] = $this->Conf->get("db.password");
        }
        if ($this->Conf->get("db.host")) {
            $conParam['host'] = $this->Conf->get("db.host");
        }
        if ($this->Conf->get("db.port")) {
            $conParam['dbname'] = $this->Conf->get("db.name");
        }
        if ($this->Conf->get("db.type")) {
            $conParam['driver'] = $this->Conf->get("db.type");
        }
        if ($this->Conf->get("db.file")) {
            $conParam['path'] = $this->Conf->get("www.dir") . $this->Conf->get("db.file");
        }
        return $conParam;
    }

}