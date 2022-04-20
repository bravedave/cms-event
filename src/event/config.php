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
	const cms_event_db_version = 1.71;

  const calendar_global = 1;

  const calendar_sales = 11;
  const calendar_rental = 12;

  const calendars = [
    'Global' => self::calendar_global,
    'Sales' => self::calendar_sales,
    'Rentals' => self::calendar_rental,

  ];

	const notify_reminder = 1;
	const notify_reminder_dismissed = 2;

	const system_events = [
		'Appointment',
		'Bulk Email',
		'Email',
		'Email Inbound',
		'Email Noted',
		'Email xTag',
		'EmailSent',
		'Info',
		'Inspect',
		'inspect-pending',
		'Lead Archive',
		'List Prop',
		'OH Inspect',
		'OH Inspect - Casual',
		'PH Call',
		'phone-in',
		'Reminder',
		'RE Enquiry',
		'REEnq',
		'Buy Enq',
		'Prop Enq',
		'sms-arrears',
		'SMS',
		'SMS IN',
		'Task',
		'p4s', 'p2s',
		'Cont Sign',
		'Cont Cond',
		'Cont Sett',
		'webdoc'

	];

	static $_CMS_EVENT_DEVELOPER = false;
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
      'cms_event_version' => self::$_CMS_EVENT_VERSION,
      'cms_event_developer' => self::$_CMS_EVENT_DEVELOPER,

    ];

		if ( file_exists( $config = self::cms_event_config())) {

      $j = (object)array_merge( $_a, (array)Json::read( $config));

      self::$_CMS_EVENT_VERSION = (float)$j->cms_event_version;
      self::$_CMS_EVENT_DEVELOPER = (float)$j->cms_event_developer;

		}

	}

}

config::cms_event_init();
