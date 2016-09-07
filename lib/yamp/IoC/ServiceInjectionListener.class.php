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


class ServiceInjectionListener implements PropertyInjectionListener, SetterInjectionListener {

	//This listener can 
	private $annotation = 'Service';

	public function injectAnnotatedProperty($YampServiceContainerBuilder, $refProperties, $service) {
		foreach($refProperties as $property) {
			$injRef = new ReflectionAnnotatedProperty($property->class, $property->name);
			$serviceIdToInject = $injRef->getAnnotation($this->annotation)->value;

			if($serviceIdToInject) {
				$prop = $property->name;
                                $propRef = new ReflectionProperty($service, $prop);
                                $propRef->setAccessible(true);
                                $propRef->setValue($service, $YampServiceContainerBuilder->getService($serviceIdToInject));
			}

		}
	}
	
	
	public function injectAnnotatedSetter($YampServiceContainerBuilder, $refMethods, $service) {

		foreach($refMethods as $method) {
			
			$injRef = new ReflectionAnnotatedMethod($method->class, $method->name);
			$serviceIdToInject = $injRef->getAnnotation($this->annotation)->value;

			if($serviceIdToInject) {
				$mName = $method->name;
				$service->$mName($YampServiceContainerBuilder->getService($serviceIdToInject));
			}

		}
	}

	public function getAnnotation() {
		return $this->annotation;
	}
}
