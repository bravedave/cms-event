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

$dbc = \sys::dbCheck( 'diary_events');
$dbc->defineField( 'event_name', 'varchar');
$dbc->defineField( 'system_event', 'tinyint');
$dbc->defineField( 'prospective_seller', 'tinyint');
$dbc->defineField( 'appointment_inspection', 'tinyint');
$dbc->defineField( 'multi_day', 'tinyint');
$dbc->defineField( 'event_type', 'varchar', 20 );
$dbc->defineField( 'order', 'varchar', 3);
$dbc->defineField( 'icon', 'varchar', 3);
$dbc->defineField( 'exclude_for_user', 'varchar', 128);
$dbc->defineField( 'calendar', 'int');
$dbc->defineField( 'global', 'tinyint');
$dbc->defineField( 'inactive', 'tinyint');
$dbc->check();

$dao = new \cms\event\dao\diary_events;
$dao->populate_defaults();
