<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace cms\event;

use Json;

class config extends \config {
  const label = 'CMS Event';
	const cms_event_db_version = 1;

  const calendar_global = 1;

  const calendar_sales = 11;
  const calendar_rental = 12;

  static protected $_CMS_EVENT_VERSION = 0;

	static protected function cms_event_version( $set = null) {
		$ret = self::$_CMS_EVENT_VERSION;

		if ( (float)$set) {
			$j = Json::read( $config = self::cms_event_config());

			self::$_CMS_EVENT_VERSION = $j->cms_event_version = $set;

			Json::write( $config, $j);

		}

		return $ret;

	}

	static function cms_event_checkdatabase() {
		if ( self::cms_event_version() < self::cms_event_db_version) {
      $dao = new dao\dbinfo;
			$dao->dump( $verbose = false);

			config::cms_event_version( self::cms_event_db_version);

		}

	}

	static function cms_event_config() {
		return implode( DIRECTORY_SEPARATOR, [
      rtrim( self::dataPath(), '/ '),
      'cms_event.json'

    ]);

	}

  static function cms_event_init() {
    $_a = [
      'cms_event_version' => self::$_CMS_EVENT_VERSION

    ];

		if ( file_exists( $config = self::cms_event_config())) {

      $j = (object)array_merge( $_a, (array)Json::read( $config));

      self::$_CMS_EVENT_VERSION = (float)$j->cms_event_version;

		}

	}

}

config::cms_event_init();
