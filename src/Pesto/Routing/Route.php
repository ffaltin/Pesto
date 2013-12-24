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

namespace Pesto\Routing;

/**
 * Router: match url to route and generate url from route
 */
class Route {

	const DEFAULT_PARAM_PATTERN = '[\\w.-]+';

	/**
	 * Controller.action
	 */
	protected $target;

	/**
	 * Pattern: /:lang/:controller/hello | array(lang => $pattern)
	 */
	protected $urlPattern;

	/**
	 * Always an array
	 */
	protected $urlRegex;

	/**
	 * array(
	 *   ':param' => array(
	 *     'pattern' => '\\w+',
	 *     'default' => null,
	 *   ),
	 * )
	 */
	protected $parameters = array();

	/**
	 * Creates a route
	 */
	public function __construct($target, $pattern, $paramInfo = array()) {
		$this->urlPattern = $pattern;
		$this->urlRegex = (array) $pattern;
		$this->target = $target;

		// Easier to write...
		foreach ($paramInfo as $k => $param) {
			$paramInfo[$k] = (array) $param;
		}

		$parameters = array();
		foreach ((array) $pattern as $p) {
			preg_match_all('/:(?P<param>\\w+),?/i', $p, $m);
			$parameters = array_merge($parameters, $m['param']);
		}
		foreach ($parameters as $param) {
			$paramPattern = sprintf('(?P<%s>%s)',
				$param,
				isset($paramInfo[$param][0])
					? $paramInfo[$param][0]
					: self::DEFAULT_PARAM_PATTERN
			);
			$default = isset($paramInfo[$param][1]) ? $paramInfo[$param][1] : null;
			if (!is_null($default)) {
				$paramPattern .= '?';
			}
			$this->parameters[$param] = array(
				'pattern' => $paramPattern,
				'default' => $default,
			);
			foreach ($this->urlRegex as $lang => $regex) {
				$this->urlRegex[$lang] = str_replace(':'.$param.',', $paramPattern, $regex);
				$this->urlRegex[$lang] = str_replace(':'.$param, $paramPattern, $regex);
			}
		}
	}

	protected function isI18n() {
		return is_array($this->urlPattern);
	}

	public function match($url) {
		foreach ($this->urlRegex as $lang => $urlRegex) {
			if ($found = preg_match('#^'.$urlRegex.'$#', $url, $m)) {
				break;
			}
		}
		if (!$found) {
			return false;
		}
		$parameters = array();
		foreach ($this->parameters as $param => $paramInfo) {
			$value = isset($m[$param]) ? $m[$param] : $paramInfo['default'];
			$parameters[$param] = $value;
			$this->target = str_replace(':'.$param.',', $value, $this->target);
			$this->target = str_replace(':'.$param, $value, $this->target);
		}
		$lang = $this->isI18n() ? $lang : null;
		return array($this->target, $parameters, $lang);
	}

	public function generate($parameters, $lang = null) {
		if ($this->isI18n()) {
			if (!$lang || !isset($this->urlPattern[$lang])) {
				throw new \InvalidArgumentException('Internationalized pattern but given language is invalid');
			}
			$pattern = $this->urlPattern[$lang];
		} else {
			$pattern = $this->urlPattern;
		}
		$pattern = preg_replace(':[?]:', null, $pattern);
		foreach ($this->parameters as $param => $paramInfo) {
			if (isset($parameters[$param])) {
				$value = $parameters[$param];
				unset($parameters[$param]);
			} else {
				$value = $paramInfo['default'];
			}
			$pattern = str_replace(':'.$param.',', $value, $pattern);
			$pattern = str_replace(':'.$param, $value, $pattern);
		}
		return (sizeof($parameters) > 0)
			? sprintf('%s?%s', $pattern, http_build_query($parameters))
			: $pattern
		;
	}
}
