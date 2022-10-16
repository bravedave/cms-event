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

  abstract function BuildContactInfoFromUser();

  abstract function renderInline();

}