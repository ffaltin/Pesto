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

/**
 * Registry: 
 */
class Registry implements \ArrayAccess, \Countable, \IteratorAggregate {

	protected $registry = array();
	protected $_keys = array();
	/**
	 * Constructor
	 */
	public function fromArray(array $data = array()) {
		$this->registry = array_merge($this->registry, $data);
	}

	public function toArray() {
		return (array) $this->registry;
	}

	/**
	 * Global
	 */
	public function getKeys() {
		return array_keys($this->registry);
	}

	public function hasKey($key) {
		return in_array($key, $this->_keys);
	}

	public function hasValue($value) {
		return in_array($value, $this->registry);
	}

	public function set($key, $value) {
		$this->registry[$key] = $value;
		return $this;
	}

	public function get($key) {
		return array_key_exists($key, $this->registry)
			? $this->registry[$key]
			: null
		;
	}

	/**
	 * Magic functions
	 */
	public function __set($key, $value) {
		$this->set($key, $value);
	}

	public function __get($key) {
		return $this->get($key);
	}

	public function __isset($key) {
		return isset($this->_stort[$key]);
	}

	public function __unset($key) {
		if (array_key_exists($key, $this->registry)) {
			unset($this->registry[$key]);
		}
	}

	/**
	 * ArrayAccess
	 */
	public function offsetSet($key, $value) {
		$this->set($key, $value);
	}

	public function offsetGet($key) {
		return $this->get($key);
	}

	public function offsetExists($key) {
		return isset($this->_stort[$key]);
	}

	public function offsetUnset($key) {
		if (array_key_exists($key, $this->registry)) {
			unset($this->registry[$key]);
		}
	}

	/**
	 * Countable
	 */
	public function count() {
		return count($this->registry);
	}

	/**
	 * IteratorAggregate
	 */
	public function getIterator() {
		return new \ArrayIterator($this->registry);
	}
}
