<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace cms\event\dao;

use green;

class users extends green\users\dao\users {
	public function getActive( $fields = 'id, name, email, mobile', $order = 'ORDER BY name' ) {
		$_sql = sprintf( 'SELECT %s FROM users WHERE active > 0 AND name != "" %s', $fields, $order );
		return $this->Result( $_sql);

	}

}