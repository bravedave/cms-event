<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

abstract class emailTemplate {

  public $messageSpace = '';

  abstract function BuildContactInfoFromUser();

  abstract function renderInline();

}