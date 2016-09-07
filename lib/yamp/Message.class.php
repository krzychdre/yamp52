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



//¿eby ³atwiej by³o siê rozeznaæ proszê pos³ugujcie siê tymi sta³ymi.
define(MESSAGE_OK, 1);
define(MESSAGE_WARN, 2);
define(MESSAGE_ERR, 3);
/**
 *
 * @author krzych
 * @return ResultObj
 */
class Message {

	//string  "enum" ok/warn/err
	private $status;

	//string description
	private $msg;

	//array of Messages
	private $messages = array();

	//mixed - any kind of result, not mandatory
	private $result;

	public function __construct($status, $msg='', $result=NULL) {
		$this->status = $status;
		$this->msg = $msg;
		$this->result = $result;
	}

	/**
	 * wyciaga status dbajac o zagniezdzone Message - mozna sobie zalesic teren, jak ktos chce
	 * @return unknown_type
	 */
	public function getStatus() {
		$status = $this->status;
		if(!empty($this->messages)) {
			foreach($this->messages as $idx => $message) {
				if($message->getStatus() > $status) {
					$status = $message->getStatus();
				}
			}
		}
		return $status;
	}

	/**
	 *
	 * @param Message $message
	 * @return void
	 */
	public function merge($message) {
		//mamy MESSAGE_OK, ale pusta wiadomosc i mergeujemy inne OK
		//wtedy skopiuj msg innej do nas
		if($message->getStatus() == MESSAGE_OK && !$this->msg) {
			$this->msg = $message->getMsg();
		}
		//nowa ma status > MESSAGE_OK, dodaj ja
		//do arraya Messagy do pozniejszego wyciagania np. statusu
		if($message->getStatus() > MESSAGE_OK)  {
			$this->messages[] = $message;
		}
	}

	public function getMsg() {
		return $this->msg;
	}

	public function getResult() {
		return $this->result;
	}

	public function getAllResults() {
		$wynik = array();
		if($this->result) {
			$wynik = $this->result;
		}
		if(!empty($this->messages)) {

			foreach($this->messages as $midx => $message) {

				if( $message->getResult() ) {
					$wynik[] = $message->getResult();
				}
			}
		}
		return $wynik;
	}

	public function setResult($result) {
		$this->result = $result;
	}

	public function appendMsg( $appendMsg ) {
		$this->msg .= $appendMsg;
		return $this;
	}

	public function getHtmlMsg($onlyLastOne=false) {
		//mapuj wartosci stalych na style css
		switch($this->status) {
			case MESSAGE_OK:	$style='ok'; break;
			case MESSAGE_WARN: 	$style='warn'; break;
			case MESSAGE_ERR: 	$style='err'; break;
		}

		/**
		 * zwroc html z message tylko jesli jest co wyswietlac, a dodatkowo wartosc zmiennej status jest zbiezna z wynikiem f-cji getStatus
		 * (patrz f-cja getStatus!)
		 */
		if($this->msg && ($this->status == $this->getStatus() ) ) {
			$result = "<table cellpadding='0' cellspacing='0' width='100%'>
							<tr class='{$style}'>
								<td align='center'>{$this->msg}</td>
							</tr>
						</table>";
		}

		if(!empty($this->messages)) {
			if($onlyLastOne) {
				$result = $this->messages[sizeof($this->messages)-1]->getHtmlMsg();
			} else {
				foreach($this->messages as $idx => $message) {
					$result .= $message->getHtmlMsg();
				}
			}
		}
		return $result;
	}


	/**
	 * szukaj needle we wszystkich wynikach Message'a rekursywnie
	 * @param $needle
	 * @param $haystack
	 * @return boolean
	 */
	public function inResults($needle, $haystack = null) {
		if(is_null($haystack)) {
			$haystack = $this->getAllResults();
		}
		if(is_array($haystack)) {
			foreach($haystack as $ids => $row) {
				if(!is_null($row)){// jak nie ma nic w kluczu to nic tam nie szukaj
					if($this->inResults($needle, $row) ) {

						return true;
					}
				} elseif(is_null($needle)){ // ale jakby ktos szuka³ nulla
					return true;
				}
			}
		} else {
			if($needle == $haystack) {
				return true;
			}
		}
		return false;
	}

	/**
	 * serializujemy czesto ten obiekt, soo.... php musi wiedziec jak
	 * @return array
	 */
	public function __sleep() {
		return array_keys( get_object_vars( $this ) );
	}
}
