<?php

/*
 * Copyright 2013 Frédéric Faltin <frederic.faltin@alpagastudio.be>
 *
 *  This file is part of Pesto.
 *
 *  Pesto is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Pesto is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Pesto.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Pesto\Util;

class Autoloader {

	protected $_paths = array();
	protected $_namespaces = array();
	protected $_custom = array();

	public function __construct() {
	}

	/**
	 * Autoload path definition
	 */
	public function addPath($path) {
		$this->_paths[] = $path;
		return $this;
	}

	/**
	 * PSR-0 namespace definition
	 */
	public function addNamespace($vendor, $path) {
		$this->_namespaces[$vendor] = $path;
		return $this;
	}

	/**
	 * PSR-0 multiple namespaces definition
	 */
	public function addNamespaces(array $namespaces) {
		$this->_namespaces = array_merge($this->_namespaces, $namespaces);
		return $this;
	}

	/**
	 * Custom autoload, using a PCRE pattern
	 */
	public function addCustom($regex, $path, $separator = '_', $suffix = '.php') {
		if (in_array(substr($path, -1, 1), array('\\', '/', DIRECTORY_SEPARATOR))) {
			$path = substr($path, 0, -1);
		}
		$this->_custom[] = array(
			'regex' => $regex,
			'path' => $path,
			'separator' => $separator,
			'suffix' => $suffix,
		);
		return $this;
	}

	public function register() {
		$this->registerNamespaces();
		$this->registerCustom();
		$this->registerPaths();
		return $this;
	}

	public function registerNamespaces() {
		$namespaces = $this->_namespaces;
		spl_autoload_register(function($class) use ($namespaces) {
			foreach ($namespaces as $vendor => $path) {
				$vendorPattern = str_replace('\\', '\\\\', $vendor);
				if (preg_match(":^{$vendorPattern}(\\\\|$):", $class)) {
					$path =
						$path.DIRECTORY_SEPARATOR.
						str_replace('\\', DIRECTORY_SEPARATOR, $class).
						'.php'
					;
					if (file_exists($path)) {
						return require $path;
					}
				}
			}
		});
		$this->_namespaces = array();
		return $this;
	}

	public function registerCustom() {
		$custom = $this->_custom;
		spl_autoload_register(function($class) use ($custom) {
			foreach ($custom as $namespace) {
				if (preg_match($namespace['regex'], $class)) {
					$path =
						$namespace['path'].
						DIRECTORY_SEPARATOR.
						str_replace(
							$namespace['separator'],
							DIRECTORY_SEPARATOR,
							$class
						).
						$namespace['suffix']
					;
					if (file_exists($path)) {
						require $path;
					}
				}
			}
		});
		$this->_custom = array();
		return $this;
	}

	public function registerPaths() {
		$paths = $this->_paths;
		spl_autoload_register(function($class) use ($paths) {
			foreach ($paths as $path) {
				$path =
					$path.
					DIRECTORY_SEPARATOR.
					str_replace('\\', DIRECTORY_SEPARATOR, $class).
					'.php'
				;
				if (file_exists($path)) {
					require $path;
				}
			}
		});
		$this->_paths = array();
		return $this;
	}
}
