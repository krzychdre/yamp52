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


require_once 'TokenFunctionReflection.class.php';
require_once 'TokenClassReflection.class.php';

class TokenParser {

	private $classBlock;
	private $classRef;
	private $fncRef;
	private $tokens = array();
	private $secured = array();
	
	private $securityListener;
	
	public function setSecurityListener($listener) {
		$this->securityListener = $listener;
	}
	
	public function getParsedClass() {
		return $this->classRef;
	}
	
	public function getTokens() {
		return $this->tokens;
	}
	
	public function injectToken($tokenId, $injectString) {
		$tmpTokens = array();
		foreach($this->tokens as $idt => $token) {
			if($idt == $tokenId) {
				$tmpTokens[] = "\n".trim($injectString,"\n")."\n";
			} else {
				$tmpTokens[] = $token;
			}
			unset($this->tokens[$idt]);
		}
		$this->tokens = $tmpTokens;
		unset($tmpTokens);
	}
	
	public function replaceToken($tokenIds, $replaceString) {
		throw new Exception("Not implemented yet");
		die;
	}
	
	public function parse($dir, $fileName) {

		$source = file_get_contents($dir.DIRECTORY_SEPARATOR.$fileName);
		$this->tokens = token_get_all($source);

		//counter of { and } for method
		$fncCurly = 0;
		
		//counter of ( and ) for method
		$fncBracket = 0;
		
		foreach ($this->tokens as $tokenId => $token) {
			if (is_string($token)) {
				// simple 1-character token
				
				//add function parameter def (string token)
				if($fncblock && $fncBracket && $fncCurly==0 && $token!='(' && $token!=')') {
					$fncParam .= $token;
				}

				if($fncblock && $fncBracket==0 && $fncParam) {
						$this->fncRef->addParameter($fncParam);
						unset($fncParam);
					}

				//add ready param to function
					if($fncBlock && $fncBracket && $fncCurly == 0) {
						$this->fncRef->addParameter($fncParam);
						unset($fncParam);
					}
				
				if($fncblock && $token == '(') { $fncBracket++; }
				if($fncblock && $token == ')') { $fncBracket--; } 
				
				//if we have opened function body
				if($fncblock && $token =='{') { $fncCurly++; }
				if($fncblock && $token =='}') { 
					$fncCurly--;
					//end of function body
					if($fncCurly == 0) {
						$fncblock = 0;
						$this->fncRef->setEndToken($tokenId);
						$this->classRef->addFunction($this->fncRef);
						unset($this->fncRef);
					} 
				}


				//add string token to method body
				if($fncblock && $fncCurly>0) {
					$this->fncRef->addToBody($token);
				}
				
			} else {
				// token is an array
				list($id, $text) = $token;
				
				//add function parameter def (array token)
				if($fncblock && $fncBracket && !$fncCurly) {
					$fncParam .= $text;
				}
				
				//add string part of token to method body (array token)
				if($fncblock && $fncCurly>0) {
					if(!$this->fncRef->getStartToken()) {
						$this->fncRef->setStartToken($tokenId);
					}
					$this->fncRef->addToBody($text);
				}
				
				if($fncblock && $token == ')') { 
					$fncBracket--; 
					
					if($fncBracket == 0) {
						$this->fncRef->addParameter($fncParam);
						unset($fncParam);
					}
				} 
				
				switch($id) { //id is a constant that represents token's type
					case T_CLASS: //class keyword
						if(is_object($this->classRef)) {
							throw new Exception("Second class definition in file.");
							die;
						}
						$classDef = 1;
						$this->classRef = new TokenClassReflection();
						break;

					case T_STRING: //class/method name
						if($classDef) {
							$this->classRef->setName($text);
							$classDef = 0;
						}
						//fncDef represents morphology part of function without params
						if($fncDef) {
							$this->fncRef->setName($text);
							if($text == $this->classRef->getName() || preg_match('/__construct/i', $text)) {
								$this->fncRef->setAsConstructor();
							}
							$fncDef = 0;
						}
					break;

					//access modifiers
					case T_PUBLIC: //public
						$modType = 'public';
						$modifier = 1;	
						break;
					case T_PROTECTED: //protected
						$modType = 'protected';
						$modifier = 1;	
						break;
					case T_PRIVATE: //private
						$modType = 'private';
						$modifier = 1;	
						break;
					case T_VAR: //var
						$modType = 'var';
						$modifier = 1;	
						break;
					
					case T_VARIABLE: //variable name
						if($modifier && !$fncblock && $this->classRef) {
							$this->classRef->addParameter($modType, $text);
							$modifier = 0;
						}
						break;
					
					case T_DOC_COMMENT: //parse any annotations
						if(is_object($this->securityListener)) {
							$this->securityListener->parseAnnotation($text);
						}
						break;

					case T_FUNCTION: //function keyword
						$this->fncRef = new TokenFunctionReflection();
						if(is_object($this->securityListener)) {
							$this->securityListener->setSecurityRequirements($this->fncRef);
						}
						$this->fncRef->setModifier($modType);
						$fncDef = 1;
						$fncblock = 1;
						break;
				}
			}
		}
	}

}