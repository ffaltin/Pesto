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

	namespace Pesto\Handling;

	use Pesto\View\View as View;
	
	class Controller {
	
		private $application;
		private $hideLayout = false;
	
		public function __construct () {
			
		}
		
		public function defineApplication($application) {
			$this->application = $application;
		}
		
		public function addToLayout(array $assignLayout) {
			$arr = $assignLayout;
			$this->application->getLayout()->assign($arr);
			return $this;
		}

		public function hideLayout() {
			$this->hideLayout = true;
		}

		// 
		public function createView($view,$assign) {
			$arr = [];
			$arr["content"] = (new View($this->application->getPathApp() . "/views/{$view}.phtml"))->assign($assign)->render();
			if ($this->hideLayout) return new Response($arr['content']);
			return $this->application->getLayout()->assign($arr)->render();
		}
	
		public function notFound() {
			return $this->application->notFound();
		}
		
		protected function getRepo($name) {
			return $this->application->getRepository($name);
		}
		
	}
