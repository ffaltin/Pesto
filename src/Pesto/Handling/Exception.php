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

class Exception extends \Exception {

	public function __construct($value) {
		print $this->displayError($value);
		die();
	}
	
	private function displayError($value) {
		return "<html><head></head><body style='background: #fafafa'><div style='background: #efefef; border: 1px solid #fefefe; padding: 15px 20px; color: #444'>Error Exception :: {$value}</div></body></html>";
	}

}
