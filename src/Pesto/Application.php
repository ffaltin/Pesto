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

	namespace Pesto;
	
	use Pesto\Routing\Router as Router;
	use Pesto\View\View as View;
	use Pesto\Handling\Exception as pException;
	use Pesto\Storage\Repository as Repository;
	use Pesto\Util\Cryptography as Cryptography;
	
	class Application {
		private $router = null;
		private $globaleView = null;
		private $args;
		private $baseLayoutName;
		private $pathApp = null;
		private $services = array();
		private $repositories = array();
		private $defaultConfigs = array("themeName"=>"pure","baseLayoutName" => "base","language" => "en");
		
		public function __construct() {
			$this->router = new Router($this);
			$this->args = ["title" => "","language"=>"en"];
		}
		
		public function setConfig(array $configs) {
			$this->defaultConfigs = (object)array_merge($this->defaultConfigs,$configs);
			if (is_null($this->defaultConfigs->pathApp)) $this->getException("pathApp must be defined");
			$this->defaultProperties();
			return $this;
		}
		
		public function defaultProperties () {
			$this->router->set404(function() {
				header('HTTP/1.1 404 Not Found');
				print (new View($this->defaultConfigs->pathApp . "/views/system/404.phtml"))->render();
			});
			$this->globalView = new View($this->defaultConfigs->pathApp . "/views/layouts/{$this->defaultConfigs->baseLayoutName}.phtml");
			$this->globalView->assign(array(
				"theme" => $this->getTheme(),
				"appName" => $this->getAppName(),
				"language" => $this->getLanguage(),
			));
		}
		// Getter
		public function getRouter() {
			return $this->router;
		}
		
		public function getLanguage() {
			return $this->defaultConfigs->language;
		}
		
		public function getAppName() {
			return $this->defaultConfigs->appName;
		}
		
		public function getTheme() {
			return $this->defaultConfigs->themeName;
		}
		
		public function getPathApp() {
			return $this->defaultConfigs->pathApp;
		}
		
		public function getLayout() {
			return $this->globalView;
		}
		
		public function getDatabase() {
			if (isset($this->services['database'])) return $this->services['database'];
		}
		
		public function getRepository($name) {
			if (!isset($this->services['database'])) $this->getException("You must define a database with defineDatabase function");
			if (isset($this->repositories[$name])) return $this->repositories[$name];
			return $this->repositories[$name] = new Repository($this->services['database'],$name);
		}
		
		/* Routing */
		public function match($uri,$fn,$type="GET|POST") {
			$this->router->match($type,$uri,$fn);
			return $this;
		}
		
		public function get ($uri,$fn) {
			$this->router->get($uri,$fn);
			return $this;
		}
		
		public function mount($uri,$fn) {
			$this->router->mount($uri,$fn);
			return $this;
		}
		
		public function post ($uri,$fn) {
			$this->router->post($uri,$fn);
			return $this;
		}
		/* Exception */
		public function notFound() {
			return $this->router->go404();
		}
		
		public function getException($value) {
			throw new pException($value);
			die;
		}
		// Views
		public function createView($view) {
			return new View($this->defaultConfigs->pathApp . "/views/{$view}.phtml");
		}
		// Services
		public function addService($name,$service) {
			if (isset($this->services[$name])) $this->getException("Service {$name} already defined");
			$this->services[$name] = $service;
			return $this;
		}
		
		public function getService($name) {
			if (!isset($this->services[$name])) $this->getException("The Service {$name} doesn't exist");
			return $this->services[$name];
		}
		
		public function defineDatabase($db) {
			if (!($db instanceof \Pesto\Storage\Database)) $this->getException("The database must be an instance of Pesto\Storage\Database");
			$this->services['database'] = $db;
			return $this;
		}
		
		// Init
		public function run() {
			$globalView = $this->globalView;
			return $this->router->run(function() use ($globalView) {
				print $globalView->render();
			});
		}
	}
	