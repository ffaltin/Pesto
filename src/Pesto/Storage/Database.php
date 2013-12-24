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
 
	namespace Pesto\Storage;
	
	use PDO;
	
	class Database extends PDO {
	   
		private $engine;
		private $host;
		private $database;
		private $user;
		private $pass;
	   
		public function __construct($user,$pass,$database,$host="localhost",$engine="pgsql") {
			$this->engine = ($engine=="postgres")?"pgsql":$engine;
			$this->host = $host;
			$this->database = $database;
			$this->user = $user;
			$this->pass = $pass;
			// prepare config
			$dns = $this->engine.':dbname='.$this->database.";host=".$this->host;
			try {
				parent::__construct( $dns, $this->user, $this->pass );
			} catch(PDOException $e) {
				return $e->getMessage();
			}
		}
	}
	
