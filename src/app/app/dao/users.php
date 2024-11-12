<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace dao;

use green;

class users extends green\users\dao\users {

  public function getActive($fields = 'id, name, email, mobile', $order = 'ORDER BY name'): array {
    return parent::getActive($fields, $order);
  }
}
