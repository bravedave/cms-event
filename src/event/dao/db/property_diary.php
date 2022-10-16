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
$dbc->defineField( 'attendants', 'text');
$dbc->defineField( 'notify_users', 'text');
$dbc->defineField( 'notify_message', 'text');
$dbc->defineField( 'notify_reminder', 'int');
$dbc->defineField( 'target_user', 'int');
$dbc->defineField( 'href', 'varchar', 120);
$dbc->check();
