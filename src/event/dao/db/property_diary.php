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
$dbc->defineField( 'notify_users', 'varchar');
$dbc->defineField( 'notify_message', 'text');
$dbc->defineField( 'target_user', 'int');
$dbc->check();
