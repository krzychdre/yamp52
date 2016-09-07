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


class TokenSecurityAOPListener implements SecurityAOPListener {

	private $annotation = 'Secured';

	private $securityRequirements;

	public function parseAnnotation($text) {
		$list = explode("\n", $text);
		if(!empty($list)) {
			foreach($list as $lidx => $lrow) {
				if(preg_match("/@".$this->annotation."/", $lrow)) {
					$sec = explode("@".$this->annotation."(", $lrow);
					$sec = explode(")",$sec[1]);
					$this->securityRequirements[] = $sec[0];
				}
			}
		}
	}

	public function setSecurityRequirements(TokenFunctionReflection $fncRef) {
		if(!empty($this->securityRequirements)) {
			$fncRef->setSecurityReq($this->securityRequirements);
			$this->securityRequirements = null;
		}

	}

	public function getSecurityAdvices(TokenFunctionReflection $function) {
		$secReq = $function->getSecurityReq();
		$result = array();

		if(!empty($secReq)) {
			foreach($secReq as $idx =>$row) {
				$req = explode("=",$row);
				$kind = trim($req[0]);
				$req = r_trim(explode(",", str_replace('"',"",$req[1])));
				switch($kind) {
					case 'require_acl':
						$secFunction = 'hasAcl';
						break;
					case 'require_role':
						$secFunction = 'hasRole';
						break;
				}
				
				if(!empty($req)) {
					$sub_result = array();
					foreach($req as $idr => $ror) {
						$sub_result[] = '$Sec->'.$secFunction."('".$ror."')";
					}
					$result[] = "(".implode(" || ",$sub_result).")";
				}
			}
		}
		if(!empty($result)) {
			$ret = '
		{
		$Sec = MainFactory::getServiceContainer()->SecurityContainer;
			if(!('.implode(" || ",$result).')) {
				$e = new MethodInsufficientPermissionsException("Insufficient permissions to call: ".__CLASS__."->".__FUNCTION__); 
				DispatcherController::exceptionEnvelope($e); 
			}
		}';
		}
		return $ret;
	}
}
