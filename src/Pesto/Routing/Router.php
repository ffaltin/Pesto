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

class Router {

	private $routes = array();
	private $befores = array();
	private $notFound;
	private $baseroute = '';
	private $method = '';
	private $application;
	
	public function __construct ($application=null) {
		$this->application = $application;
	}

	public function before($methods, $pattern, $fn) {

		$pattern = $this->baseroute . '/' . trim($pattern, '/');
		$pattern = $this->baseroute ? rtrim($pattern, '/') : $pattern;

		foreach (explode('|', $methods) as $method) {
			$this->befores[$method][] = array(
				'pattern' => $pattern,
				'fn' => $fn
			);
		}

	}

	public function match($methods, $pattern, $fn) {

		$pattern = $this->baseroute . '/' . trim($pattern, '/');
		$pattern = $this->baseroute ? rtrim($pattern, '/') : $pattern;

		foreach (explode('|', $methods) as $method) {
			$this->routes[$method][] = array(
				'pattern' => $pattern,
				'fn' => $fn
			);
		}

	}
	
	public function get($pattern, $fn) {
		$this->match('GET', $pattern, $fn);
	}

	public function post($pattern, $fn) {
		$this->match('POST', $pattern, $fn);
	}

	public function patch($pattern, $fn) {
		$this->match('PATCH', $pattern, $fn);
	}

	public function delete($pattern, $fn) {
		$this->match('DELETE', $pattern, $fn);
	}

	public function put($pattern, $fn) {
		$this->match('PUT', $pattern, $fn);
	}

	public function options($pattern, $fn) {
		$this->match('OPTIONS', $pattern, $fn);
	}

	// making subroutes on routes
	public function mount($baseroute, $fn) {
		$curBaseroute = $this->baseroute;
		$this->baseroute .= $baseroute;
		call_user_func($fn);
		$this->baseroute = $curBaseroute;
	}

	public function getRequestHeaders() {

		// getallheaders available, use that
		if (function_exists('getallheaders')) return getallheaders();

		// getallheaders not available: manually extract 'm
		$headers = array();
		foreach ($_SERVER as $name => $value) {
			if ((substr($name, 0, 5) == 'HTTP_') || ($name == 'CONTENT_TYPE') || ($name == 'CONTENT_LENGTH')) {
				$headers[str_replace(array(' ', 'Http'), array('-', 'HTTP'), ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
			}
		}
		return $headers;

	}

	public function getRequestMethod() {

		// Take the method as found in $_SERVER
		$method = $_SERVER['REQUEST_METHOD'];

		if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
			ob_start();
			$method = 'GET';
		}

		else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$headers = $this->getRequestHeaders();
			if (isset($headers['X-HTTP-Method-Override']) && in_array($headers['X-HTTP-Method-Override'], array('PUT', 'DELETE', 'PATCH'))) {
				$method = $headers['X-HTTP-Method-Override'];
			}
		}

		return $method;

	}
	public function run($callback = null) {

		// Define which method we need to handle
		$this->method = $this->getRequestMethod();

		// Handle all before middlewares
		if (isset($this->befores[$this->method]))
			$this->handle($this->befores[$this->method]);

		// Handle all routes
		$numHandled = 0;
		if (isset($this->routes[$this->method]))
			$numHandled = $this->handle($this->routes[$this->method], true);

		// If no route was handled, trigger the 404 (if any)
		if ($numHandled == 0) {
			$this->get404();
		}
		// If a route was handled, perform the finish callback (if any)
		else {
			if ($callback) $callback();
		}
		// If it originally was a HEAD request, clean up after ourselves by emptying the output buffer
		if ($_SERVER['REQUEST_METHOD'] == 'HEAD') ob_end_clean();

	}

	public function set404($fn) {
		$this->notFound = $fn;
	}

	public function get404() {
		if ($this->notFound && is_callable($this->notFound)) call_user_func($this->notFound);
		else header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
	}

	private function handle($routes, $quitAfterRun = false) {

		// Counter to keep track of the number of routes we've handled
		$numHandled = 0;

		// The current page URL
		$uri = $this->getCurrentUri();

		// Variables in the URL
		$urlvars = array();

		// Loop all routes
		foreach ($routes as $route) {

			// we have a match!
			if (preg_match_all('#^' . $route['pattern'] . '$#', $uri, $matches, PREG_OFFSET_CAPTURE)) {

				// Rework matches to only contain the matches, not the orig string
				$matches = array_slice($matches, 1);

				// Extract the matched URL parameters (and only the parameters)
				$params = array_map(function($match, $index) use ($matches) {

					// We have a following parameter: take the substring from the current param position until the next one's position (thank you PREG_OFFSET_CAPTURE)
					if (isset($matches[$index+1]) && isset($matches[$index+1][0]) && is_array($matches[$index+1][0])) {
						return trim(substr($match[0][0], 0, $matches[$index+1][0][1] - $match[0][1]), '/');
					}

					// We have no following paramete: return the whole lot
					else {
						return (isset($match[0][0]) ? trim($match[0][0], '/') : null);
					}

				}, $matches, array_keys($matches));
				
				if (gettype($route["fn"])=="string" && strlen($route['fn']) < 50) {
					$p = explode('.',$route['fn']);
					$function = $p[1]."Action";
					$class = "App\\Controller\\".ucfirst($p[0])."Controller";
					$controller = new $class();
					$controller->defineApplication($this->application);
					$controller->$function($params);
					// defineApplication
				} else if (gettype($route["fn"])!="string"){
					// call the handling function with the URL parameters
					call_user_func_array($route['fn'], $params);
				}
				$numHandled++;
				if ($quitAfterRun) break;
			}

		}

		// Return the number of routes handled
		return $numHandled;

	}

	private function getCurrentUri() {

		// Get the current Request URI and remove rewrite basepath from it (= allows one to run the router in a subfolder)
		$basepath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
		$uri = substr($_SERVER['REQUEST_URI'], strlen($basepath));

		// Don't take query params into account on the URL
		if (strstr($uri, '?')) $uri = substr($uri, 0, strpos($uri, '?'));

		// Remove trailing slash + enforce a slash at the start
		$uri = '/' . trim($uri, '/');

		return $uri;

	}

}
