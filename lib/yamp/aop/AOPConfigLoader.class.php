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



require_once 'AOPAspect.class.php';

class AOPConfigLoader {

	/**
	 * 
	 * @var AOPContainer
	 */
	private $AOPContainer;
	
	public function __construct() {
		$this->AOPContainer = MainFactory::getAOPContainer();		
	}
	
	public function doLoad($file) {
		$this->parse($this->getFileAsXml($file));
	}

	protected function parse($xml) {
		
			foreach($xml->blacklist->entry as $blacklistEntry) {
				AOP::addBlacklistEntry($blacklistEntry);
			}
			
			foreach($xml->advices->advice as $advice) {
				foreach($advice->attributes() as $attribute) {
					$adv[$attribute->getName()] = (string)$attribute;
				}
				$this->AOPContainer->addAspect(new AOPAspect($adv['pattern'], $adv['when'], $adv['class'], $adv['method'], $adv['params']));
			}
	}

	protected function getFileAsXml($path) {

			if (!file_exists($path)) {
				throw new InvalidArgumentException(sprintf('The aspects file "%s" does not exist.', $path));
			}

			$dom = new DOMDocument();
			libxml_use_internal_errors(true);
			if (!$dom->load($path)) {
				throw new InvalidArgumentException(implode("\n", $this->getXmlErrors()));
			}
			libxml_use_internal_errors(false);
			$this->validate($dom);

			$xml = simplexml_import_dom($dom);
		return $xml;
	}

	protected function validate($dom) {
		libxml_use_internal_errors(true);
		if (!$dom->schemaValidate(dirname(__FILE__).'/aopconfig.xsd')) {
			throw new InvalidArgumentException(implode("\n", $this->getXmlErrors()));
		}
		libxml_use_internal_errors(false);
	}

	protected function getXmlErrors() {
		$errors = array();
		foreach (libxml_get_errors() as $error) {
			$errors[] = sprintf('[%s %s] %s (in %s - line %d, column %d)',
			LIBXML_ERR_WARNING == $error->level ? 'WARNING' : 'ERROR',
			$error->code,
			trim($error->message),
			$error->file ? $error->file : 'n/a',
			$error->line,
			$error->column
			);
		}

		libxml_clear_errors();

		return $errors;
	}
}
