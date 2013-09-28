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

namespace Pesto\View;

class View {

	protected $path;
	protected $registry = array();
	protected $content_type = 'text/html; charset=utf-8';

	public function __construct($path, $contentType = null, $charset = null) {
		$this->path = $path;
		if (! is_null($contentType)) {
			if (! is_null($charset)) {
				$this->setContentType($contentType, $charset);
			} else {
				$this->setContentType($contentType, 'utf-8');
			}
		}
	}

	public function assign($key, $value = null) {
		if (is_array($key)) {
			foreach ($key as $k => $v) {
				$this->assign($k, $v);
			}
			return $this;
		}
		$this->registry[$key] = $value;
		return $this;
	}

	public function getData() {
		return $this->registry;
	}

	public function getKey($key) {
		return $this->registry[$key];
	}
	
	public function assignPath ($path) {
		$this->path = $path;
		return $this;
	}
	
	public function __set($key, $value) {
		$this->assign($key, $value);
	}

	
	public function __get($key) {
		return $this->registry[$key];
	}

	public function __isset($key) {
		return isset($this->registry[$key]);
	}
	
	public function setContentType($mimetype, $charset = null) {
		if (!is_null($charset)) {
			$mimetype .= '; charset='.$charset;
		}
		$this->content_type = $mimetype;
		return $this;
	}

	public function getContentType() {
		return $this->content_type;
	}

	public function render() {
		ob_start();
		header('Content-Type: '.$this->content_type);
		extract($this->registry);
		include $this->path;
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}
