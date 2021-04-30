<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace cms\event\dao;

use cms\event\config;
use cms\event\sys;
use currentUser;

use dao\_dao;

class diary_events extends _dao {
	protected $_db_name = 'diary_events';
	protected $template = __NAMESPACE__ . '\dto\diary_events';

	protected static $_buffer = false;

	protected function _isDiaryEvent( $event) {
		$this->BufferAll();
		if ( self::$_buffer) {
			foreach( self::$_buffer as $dto) {
				if ( $event == $dto->event_name) {
					return ( true);

				}

			}

		}

		return ( false);

	}

	public static function isDiaryEvent( $event) {
		$de = new self;
		return ( $de->_isDiaryEvent( $event));

	}

	protected function BufferAll() {
		if ( !self::$_buffer) {
			if ( $res = $this->getAll('event_name, icon')) {
				self::$_buffer = $res->dtoSet();

      }

		}

	}

	public function getAll( $fields = '*', $order = null ) {
    if ( !$order) {
      $_pref = [];
      if ( $preferedOrder = currentUser::diaryEventOrder()) {
        $prefs = explode(',', $preferedOrder);

        $_cal = [];
        foreach ($prefs as $pref) {
          $pref = trim( $pref);
          if ( isset( config::calendars[$pref])) {
            $_cal[] = sprintf( 'WHEN %d THEN 0', config::calendars[$pref]);

          }

        }

        if ( $_cal) {
          $_cal[] = 'ELSE 1';
          $_pref[] = sprintf( 'CASE `calendar` %s END ASC', implode( ' ', $_cal));

        }

      }

      $_pref[] = '`order` ASC';
      $_pref[] = '`event_name` ASC';

      $order = sprintf( 'ORDER BY %s', implode( ',', $_pref));

    }

    $order = 'WHERE inactive <> 1 ' . $order;

		return ( parent::getAll( $fields, $order));

	}

	public function getAllwithInactive( $fields = '*', $order = null ) {
    if ( !$order) {
      $_pref = [];
      if ( $preferedOrder = currentUser::diaryEventOrder()) {
        $prefs = explode(',', $preferedOrder);

        $_cal = [];
        foreach ($prefs as $pref) {
          $pref = trim( $pref);
          if ( isset( config::calendars[$pref])) {
            $_cal[] = sprintf( 'WHEN %d THEN 0', config::calendars[$pref]);

          }

        }

        if ( $_cal) {
          $_cal[] = 'ELSE 1';
          $_pref[] = sprintf( 'CASE `calendar` %s END ASC', implode( ' ', $_cal));

        }

      }

      $_pref[] = '`order` ASC';
      $_pref[] = '`event_name` ASC';

      $order = sprintf( 'ORDER BY %s', implode( ',', $_pref));

    }

		return ( parent::getAll( $fields, $order));

	}

  public function getCalendarEvents( int $iCal, $htmlSafe = false) : ?array {
    // I don't think this is ever used
    if ( $iCal) {
      $sql = sprintf(
        'SELECT `event_name` FROM `diary_events` WHERE `calendar` = %d',
        $iCal

      );

      if ( $res = $this->Result( $sql)) {
        $ret = [];
        if ( $htmlSafe) {
          while ( $dto = $res->dto()) $ret[] = \htmlspecialchars( $dto->event_name);

        }
        else {
          while ( $dto = $res->dto()) $ret[] = $dto->event_name;

        }

        return $ret;

      }

    }

    return null;

  }

  public function getEventByName( string $name) : ?dto\diary_events {
    $sql = sprintf( 'SELECT * FROM `diary_events` WHERE `event_name` = "%s"', $name );
    if ( $res = $this->Result( $sql)) {
      if ( $dto = $res->dto( $this->template))
        return $dto;

    }

    return null;

  }

	public function IconFor( $event) {

		$reEvents = [
			'RE Enquiry',
			'REEnq',

    ];

		if ( in_array( $event, $reEvents)) return ( config::$RE_ICON);


		if ( in_array( $event, config::system_events)) {
			return ( sprintf(  '<img src="%s" title="%s" role="icon" class="cms-icon">',  sys::IconForEvent( $event ), $event));

		}

		if ( $event == 'Rent Inspect') {
			return ( \cms\icon::rent_inspect);

		}

		$this->BufferAll();
		if ( self::$_buffer) {
			foreach( self::$_buffer as $dto) {
				if ( $event == $dto->event_name) {
					if ( $dto->icon) {
						return ( \html::icon( $dto->icon, $event));

          }
					else {
						return ( \html::icon( $dto->event_name, $event));

          }

				}

			}

		}

		return ( \html::icon( $event, $event));

	}

  public function populate_defaults() {

    if ( $res = $this->Result( 'SELECT count(*) `count` FROM diary_events' )) {
      if ( $dto = $res->dto()) {
        if ( $dto->count < 1) {
          $a = [
            'FU',
            'PH',
            'Insp',
            'Prop Enq',
            'OH',
            'OHFU',
            'List Pres',
            'Sell Enq',
            'Meet',
            'Gen Enq',
            'App',
            'Gen',
            'Pro Sell',
            'Info'

          ];

          foreach ( $a as $v ) $this->Insert(['event_name' => $v]);
          \sys::logger( 'wrote diary_events defaults');

        }

      }

    }

    if ( $dto = $this->getEventByName('Buy Insp')) {
      if ( !$dto->system_event || !$dto->appointment_inspection) {
        $this->UpdateByID([
          'system_event' => 1,
          'appointment_inspection' => 1

        ], $dto->id );

      }

    }
    else {
      $this->Insert([
				'event_name' => 'Buy Insp',
				'system_event' => 1,
				'appointment_inspection' => 1

      ]);

    }

    if ( $dto = $this->getEventByName('2nd Insp')) {
      if ( !$dto->system_event || !$dto->appointment_inspection) {
        $this->UpdateByID([
          'system_event' => 1,
          'appointment_inspection' => 1

        ], $dto->id );

      }

    }
    else {
      $this->Insert([
				'event_name' => '2nd Insp',
				'system_event' => 1,
				'appointment_inspection' => 1

      ]);

    }

    if ( $dto = $this->getEventByName('2nd Insp')) {
      if ( !$dto->system_event || !$dto->appointment_inspection) {
        $this->UpdateByID([
          'system_event' => 1,
          'appointment_inspection' => 1

        ], $dto->id);

      }

    }
    else {
      $this->Insert([
				'event_name' => 'OH Insp',
				'system_event' => 1,
				'appointment_inspection' => 1

      ]);

    }

  }

	public static function isHidden( $dto) : bool {
		if ( trim( $dto->exclude_for_user, '; ')) {
			$users = explode( ';', trim( $dto->exclude_for_user, '; '));
			if ( in_array( (int)currentUser::id(), $users)) {
				return ( true);

			}

		}

		return ( false);

	}

}
