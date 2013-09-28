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

class Repository {
	protected $db;
	protected $table;
	public $lastInsertId;
	
	public function __construct($db,$table) {
		$this->db = $db;
		$this->table = $table;
	}
		
	public function add($array) {
		try{
			$reqPrepared = $this->prepareRequest($array);
			$stmt = $this->db->prepare("insert into {$this->table} ({$reqPrepared->keys}) values ({$reqPrepared->params})");
			$stmt = $this->bindAllParams($array,$stmt);
			$stmt->execute();
			$this->lastInsertId = $this->db->lastInsertId();
			return $this;
		} catch(PDOException $e){				
			throw $e;
			return $this->db->getMessage();
		}		   						 
	}
	
	public function getBy($type,$value) {
		$stmt = $this->db->prepare("select * from {$this->table} where {$type} = :{$type}");
		$stmt->bindParam(":{$type}",$v);
		$v = $value;
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function getOrderedBy($sortBy) {
		$stmt = $this->db->prepare("select * from {$this->table} order by {$sortBy}");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	public function getOneBy($type,$value) {
		$stmt = $this->db->prepare("select * from {$this->table} where {$type} = :{$type} limit 1");
		$stmt->bindParam(":{$type}",$v);
		$v = $value;
		$stmt->execute();
		return (object)$stmt->fetch(PDO::FETCH_ASSOC);
	}
	
	public function getAll() {
		$stmt = $this->db->prepare("select * from {$this->table}");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	private function bindAllParams(array $arr,$query) {
		foreach ($arr as $key => $value) { 
			$query->bindParam(":".$key,$$value); 
			$$value = $value;
		}
		return $query;
	}
		
	private function prepareRequest($arr) {
		// get keys
		$keys = array_keys($arr);
		// prepare For the request statement, get all keys from array
		$req = array(
			// get All keys and add a colon
			"keys" => implode(',',$keys),
			// prepare params requests
			"params" => implode(',',array_map(function($value){ 
				return ':'.$value;
			},$keys))
		);
		return (object)$req;
	}
}
