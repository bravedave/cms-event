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

$dbc = \sys::dbCheck( 'property_diary' );
$dbc->defineField( 'event_name', 'varchar');
$dbc->defineField( 'target_user', 'int');
$dbc->check();
