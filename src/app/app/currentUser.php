<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

abstract class currentUser extends dvc\currentUser {
  public static function FirstName() : string {
    return config::label;

  }

}
