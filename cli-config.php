<?php

require_once 'Doctrine/Common/ClassLoader.php';

$classLoader = new \Doctrine\Common\ClassLoader('Entity', __DIR__ . '/lib/Mapping');
$classLoader->register();

$classLoader = new \Doctrine\Common\ClassLoader('Proxy', __DIR__ . '/lib/Mapping');
$classLoader->register();

$classLoader = new \Doctrine\Common\ClassLoader('DoctrineExtensions', __DIR__ . '/lib/vendor');
$classLoader->register();

$config = new \Doctrine\ORM\Configuration();
$config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache);
$config->setProxyDir(__DIR__ . '/lib/Mapping/Proxy');
$config->setProxyNamespace('Entity\Proxy');

$driverImpl = $config->newDefaultAnnotationDriver(__DIR__ . '/lib/Mapping/Entity');
$config->setMetadataDriverImpl($driverImpl);

$connectionOptions = array(
    'driver' => 'pdo_sqlite',
    'path' => 'database.sqlite');

$em = \Doctrine\ORM\EntityManager::create($connectionOptions, $config);

$helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
            'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
            'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
        ));
