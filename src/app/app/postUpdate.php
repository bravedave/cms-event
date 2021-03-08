<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

class postUpdate extends dvc\service {
  protected function _upgrade() {
    config::route_register( 'people', 'green\\people\\controller');
    config::route_register( 'property', 'green\\properties\\controller'); // for some cms compatibility
    config::route_register( 'properties', 'green\\properties\\controller');
    config::route_register( 'property_diary', 'green\\property_diary\\controller');
    config::route_register( 'beds', 'green\\beds_list\\controller');
    config::route_register( 'baths', 'green\\baths\\controller');
    config::route_register( 'property_type', 'green\\property_type\\controller');
    config::route_register( 'postcodes', 'green\\postcodes\\controller');
    config::route_register( 'users', 'green\\users\\controller');

    green\beds_list\config::green_beds_list_checkdatabase();
    green\baths\config::green_baths_checkdatabase();
    green\property_type\config::green_property_type_checkdatabase();
    green\postcodes\config::green_postcodes_checkdatabase();
    green\property_diary\config::green_property_diary_checkdatabase();
    green\users\config::green_users_checkdatabase();

    green\people\config::green_people_checkdatabase();
    green\properties\config::green_properties_checkdatabase();
    echo( sprintf('%s : %s%s', 'green updated', __METHOD__, PHP_EOL));

    cms\event\config::cms_event_checkdatabase();
    config::route_register( 'event', 'cms\event\controller');
    echo( sprintf('%s : %s%s', 'cms\event updated', __METHOD__, PHP_EOL));

  }

  static function upgrade() {
    $app = new self( application::startDir());
    $app->_upgrade();

  }

}
