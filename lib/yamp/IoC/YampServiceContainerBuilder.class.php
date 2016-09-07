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


require_once 'vendor/addendum/annotations.php';
require_once 'IoCAnnotations.php';

class YampServiceContainerBuilder extends sfServiceContainerBuilder {

    private $annotationListeners = array();

    /**
     * Creates a service for a service definition.
     * Also injects annotated fields with proper service.
     *
     * @param  sfServiceDefinition $definition A service definition instance
     *
     * @return object              The service described by the service definition
     */
    protected function createService(sfServiceDefinition $definition) {

        $service = parent::createService($definition);
        if (is_object($service)) {

            $r = new ReflectionClass($this->resolveValue($definition->getClass()));

            foreach ($this->annotationListeners as $listener) {
                $refClass = new ReflectionClass($listener);

                //injection through reflection (property injection)
                if ($refClass->implementsInterface('PropertyInjectionListener')) {
                    $listener->injectAnnotatedProperty($this, $r->getProperties(), $service);
                }

                //injection through method (setter injection)
                if ($refClass->implementsInterface('SetterInjectionListener')) {
                    $listener->injectAnnotatedSetter($this, $r->getMethods(), $service);
                }
            }
        }
        return $service;
    }

    public function addAnnotationListener($clazz) {
        $this->annotationListeners[$clazz->getAnnotation()] = $clazz;
    }

    public function delAnnotationListener($clazz) {
        unset($this->annotationListeners[$clazz->getAnnotation()]);
    }

}
