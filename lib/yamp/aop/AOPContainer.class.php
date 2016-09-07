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


class AOPContainer {

	private $Aspects = array();

	private $SecurityListener;

	/**
	 * Add an advice to the Container
	 * @param $Aspect
	 * @return unknown_type
	 */
	public function addAspect($Aspect) {
		if(!empty($this->Aspects)) {
			foreach($this->Aspects as $idx => $row) {
				if($row->getHash() == $Aspect->getHash()) {
					throw new Exception("Duplicate AOPAspect definition");
					die;
				}
			}
		}
		$this->Aspects[] = $Aspect;
	}

	/**
	 * set a SecurityListener object for
	 * secur. checking injection in annotated methods
	 *
	 * @param SecurityListener $listener
	 */
	public function setSecurityListener($listener) {
		$this->SecurityListener = $listener;
	}

	/**
	 * simple pattern matching for AOP injection
	 * @param $className
	 * @param $methodName
	 * @param $methodParams
	 * @param $pattern
	 * @return boolean
	 */
	public function matchPattern($className, $methodName, $methodParams, $pattern) {
		$parts = explode("->",$pattern);
		$class = $parts[0];
		$method = $parts[1];

		$parts = explode("(",$method);
		$params = explode(",",preg_replace("/\)/","",$parts[1]));

		$success = 0;
		if($className == '' || preg_match("/".$class."/i", $className)) {

			if($methodName && preg_match("/".$method."/i", $methodName)) {

				$success = 1;

				if(!empty($params) && empty($methodParams)) {
					$success = 0;
				}
				if(empty($params) && !empty($methodParams)) {
					$success = 0;
				}

			}
		}
		return $success;
	}

	/**
	 * determine where in the token array inject advice
	 * @param Parser TokenParser
	 * @return unknown_type
	 */
	public function injectAdvices($Parser) {
		$class = $Parser->getParsedClass();
		if(is_object($class)) {
			$functions = $class->getFunctions();
			if(!empty($functions)) {
				foreach($functions as $idx => $function) {
						
					//this will inject any calls for security check in annotated methods
					if(is_object($this->SecurityListener)) {
						$securityRequirements = $this->SecurityListener->getSecurityAdvices($function);
						if($securityRequirements) {
							$tokenId = $function->getStartToken();
							$Parser->injectToken($tokenId, $securityRequirements);

							$idOffset = sizeof(explode("\n",$securityRequirements));
							$tokenId += $idOffset;
							
							$function->setStartToken($tokenId);
						}
					}
						
					foreach($this->Aspects as $ida => $Aspect) {
						if($this->matchPattern($class->getName(), $function->getName(), $function->getParameters(), $Aspect->getPattern())) {

							if($Aspect->getWhen() == 'before') {
								$tokenId = $function->getStartToken();

							} elseif($Aspect->getWhen() == 'after') {
								$tokenId = $function->getEndToken();
							}

							if($Aspect->getWhen() == 'around') {
								$tokenIds = array($function->getStartToken(), $function->getEndToken());
								$Parser->replaceToken($tokenIds, $Aspect->getCallToInject($function));
							} else {
								$Parser->injectToken($tokenId, $Aspect->getCallToInject($function));
							}

						}
					}
				}
			}
		}
	}

	/**
	 * write parsed and injected class to disk
	 * @param $origFile
	 * @param $destFile
	 * @return unknown_type
	 */
	public function prepareAOPClass($origFile, $destFile) {
		$classOutSrc = '';

		$Parser = new TokenParser();
		if(is_object($this->SecurityListener)) {
			$Parser->setSecurityListener($this->SecurityListener);
		}

		$Parser->parse(dirname($origFile), basename($origFile));

		//tutaj dorzucanie ró¿no¶ci do tokenów :D
		$this->injectAdvices($Parser);

		//teraz regenerujemy plik
		$tokens = $Parser->getTokens();
		if(!empty($tokens)) {
			foreach($tokens as $idx => $token) {
				if(is_string($token)) {
					$classOutSrc .= $token;
				} else {
					list($id, $text) = $token;
					$classOutSrc .= $text;

					if($id == T_OPEN_TAG) {
						$classOutSrc .= "\n/**
 * This is AOP Container precompiled class.
 * 
 * Do not modify this file.
 * It will be regenerated automatically by AOP.
 * 
 */\n"; }

				}
			}
		}
		file_put_contents($destFile, $classOutSrc);
	}
}