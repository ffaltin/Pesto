<?php

namespace Pesto\Util;

use Pesto\Util\Cryptography as Cryptography;

class Authentication {
	//
	private $secretKey;
	//
	public function  __construct($secret) {
		$this->secretKey = $secret;
		return $this;
	}

	public function hashPassword ($password) {
		return Cryptography::symmetricKeyEncrypt($password, $this->getSecretKey());
	}
	
	public function checkPassword ($password,$cipher) {
		return $password == Cryptography::symmetricKeyDecrypt($cipher,$this->getSecretKey());
	}

	private function getSecretKey() {
		return $this->secretKey;
	}
}
