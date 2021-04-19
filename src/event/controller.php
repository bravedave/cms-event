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

use currentUser;
use dvc;
use green;
use Json;
use Response;
use strings;

class controller extends \Controller {
  protected $viewPath = __DIR__ . '/views/';
  protected $jCalendarFilter = null;

	protected function before() {
		config::cms_event_checkdatabase();
		parent::before();

	}

  protected function _index() {

    $dao = new dao\diary_events;
    $this->data = (object)[
      'dto' => false,
      'dataset' => $dao->getAll()

    ];

    $secondary = ['index'];

    if ( config::$_CMS_EVENT_DEVELOPER) {
      // $secondary[] = 'index-developer';

    }

		$this->render([
			'title' => $this->title = 'Diary Events',
			'primary' => 'list',
			'secondary' => $secondary

    ]);

  }

	protected function jCalendar( $iCal, $start, $end) : array {
    $debug = false;
    $ret = [];

    $autotime = '07:00:00';

		$dao = new dao\property_diary;
		if ( $dtoSet = $dao->getCalendar( $iCal, $start, $end)) {
      // sys::dump( $dtoSet);

      $filter = function( $dto) { return true; };
      if ( $this->jCalendarFilter) {
        $filter = $this->jCalendarFilter;

      }

      foreach ($dtoSet as $dto) {

        if ( !$filter( $dto)) continue;

        $start = new \DateTime( $dto->date_start == '0000-00-00 00:00:00' ? $dto->date . ' ' . $autotime : $dto->date_start);
        $end = new \DateTime( $dto->date_end == '0000-00-00 00:00:00' ? $dto->date . ' ' . $autotime : $dto->date_end);
        $diff = $end->getTimestamp() - $start->getTimestamp();

        $ret[] = [
          'title' => $dto->subject,
          'location' => $dto->location,
          'notes' => '',
          'start' => $start->format('c'),
          'end' => $diff < 1801 ? $start->format('c') : $end->format('c'),
          'id' => sprintf( 'property-diary-%d@cms-event', $dto->id),
          'allDay' => (int)('00:00' == $start->format( 'HH:mm') && '23:59' == $end->format( 'HH:mm')),
          'changekey' => '',
          'src' => 'property_diary'

        ];

      }

      // sys::dump( $ret);

    }

    return $ret;

  }

  protected function postHandler() {
    $action = $this->getPost('action');
    if ( 'appointment-delete' == $action) {
      if ( $id = (int)$this->getPost('id')) {
        $dao = new dao\property_diary;
        $dao->delete( $id);
        Json::ack( $action);

      } else { Json::nak( $action); }

    }
    elseif ( 'appointment-post' == $action) {

      $multiday = (int)$this->getPost('multiday');
      if ( $multiday) {
        $start = sprintf( '%s 00:00:00', $this->getPost('date'));
        $end = sprintf( '%s 23:59:59', $this->getPost('date_end'));

      }
      else {
        $start_time = $this->getPost('start');
        $start = sprintf( '%s %s', $this->getPost('date'), $start_time);
        $end = sprintf( '%s %s', $this->getPost('date'), $this->getPost('end'));

      }

      if ( strtotime( $start) > 0 && strtotime( $end) > strtotime( $start)) {
        $a = [
          'date' => $this->getPost('date'),
          'date_start' => date( 'Y-m-d H:i:s', strtotime( $start)),
          'date_end' => date( 'Y-m-d H:i:s', strtotime( $end)),
          'event' => 'Appointment',
          'event_name' => $this->getPost('event'),
          'property_id' => $this->getPost('property_id'),
          'people_id' => $this->getPost('people_id'),
          'location' => $this->getPost('location'),
          'comments' => $this->getPost('notes'),
          'target_user' => $this->getPost( 'target_user'),
          'user_id' => currentUser::id(),
          'updated' => \db::dbTimeStamp(),
          'update_user_id' => currentUser::id(),
          'attendants' => '',
          'notify_users' => '',
          'notify_message' => $this->getPost( 'notify_message'),

        ];

        if ( $attendants = $this->getPost('attendants')) {
          $a['attendants'] = json_encode( (array)$attendants);

        }

        if ( $notify_users = $this->getPost('notify_users')) {
          $a['notify_users'] = json_encode( (array)$notify_users);

        }

        // subject is activity - property - person

        $street = $this->getPost('address_street');
        $location = $this->getPost('location');

        $a['location'] = $location ? $location : $street;

        $name = $this->getPost('people_name');
        $_subject = [];
        if ( $a['event_name']) $_subject[] = $a['event_name'];
        if ( $street) $_subject[] = strings::GoodStreetString( $street);
        if ( $name) $_subject[] = $name;
        if ( $location) $_subject[] = sprintf('loc:%s', $location);

        $a['subject'] = implode(' - ', $_subject);

        $notifyUser = function( $suffix, $uid) use ($a) {

          $dao = new dao\users;
          if ( $uDto = $dao->getByID( $uid)) {
            $start = date( 'D d/m - ga', strtotime( $a['date_start']));

            $notes = [
              '<span style="font-family: monospace;">',
              '<strong>Appointment Details</strong>',
              '',
              'Date/time ..: ' . $start,
              'Activity ...: ' . $a['event_name']

            ];

            if ( $person = $this->getPost('people_name')) $notes[] = 'Person .....: ' . $person;
            if ( $a['location']) $notes[] = 'Location ...: ' . $a['location'];
            if ( $address = $this->getPost('address_street')) $notes[] = 'Property ...: ' . $address;
            if ( $a['comments']) $notes[] = sprintf( 'Notes ......:<blockquote style="margin-left: 1em;">%s</blockquote>', strings::text2html( $a['comments']));
            if ( $a['notify_message']) {
              $notes[] = '';
              $notes[] = sprintf( 'Instructions:<blockquote style="margin-left: 1em;">%s</blockquote>', strings::text2html( $a['notify_message']));

            }

            $signoff = currentUser::user()->name;
            if ( isset( currentUser::user()->email_signoff) && currentUser::user()->email_signoff)
              $signoff = currentUser::user()->email_signoff;

            $notes[] = '';
            $notes[] = $signoff;
            $notes[] = '</span>';

            $msgHtml = implode( '<br>', $notes);

            if ( class_exists('emailTemplate')) {
              /**
               * wrap the message in our customized templates
               **/
              $eBody = new \emailTemplate;
              $eBody->BuildContactInfoFromUser();
              $eBody->messageSpace = $msgHtml;
              $msgHtml = $eBody->renderInline();

            }

            $mail = currentUser::mailer();
            $mail->Subject = sprintf( 'Appt.%s %s - %s', $suffix, $a['event_name'], $start);
            $mail->msgHTML( $msgHtml);
            $mail->AddAddress( $uDto->email, $uDto->name);
            if ( !$mail->send()) {
              \sys::logger( sprintf('<failed to send notification> <%s> %s', $mail->ErrorInfo, __METHOD__));

            }

          }
          else {
            \sys::logger( sprintf('<failed to find user to notify>  %s', __METHOD__));

          }

        };

        $dao = new dao\property_diary;
        if ( $id = (int)$this->getPost( 'id')) {
          $dao->UpdateByID( $a, $id);
          Json::ack( $action);

          if ( $notify_users) {
            foreach ($notify_users as $user) $notifyUser( '(u)', $user);

          }

        }
        else {
          $a['created'] = $a['updated'];
          $dao->Insert( $a);
          if ( (int)$a['target_user'] && (int)$a['target_user'] != currentUser::id()) {
            if ( 'yes' == $this->getPost('notify_target_user')) {
              $dao = new dao\users;
              if ( $u = $dao->getByID( $a['target_user'])) {
                $msg = sprintf( "I have booked a %s for us on %s at %s %s%s - %s",
                  $a['event'],
                  strings::asLongDate( $a['date']),
                  $start_time,
                  $name ? sprintf( 'with %s ', $name) : '',
                  $a['location'] ? sprintf( 'at %s ', $a['location']) : '',
                  \currentUser::FirstName()

                );

                if ( \class_exists('\cms\sms')) {
                  \cms\sms::notifyUser( $a['target_user'], $msg);

                }
                else {
                  \sys::logger( sprintf( '<\cms\sms - class not found> : %s', __METHOD__));
                  \sys::logger( sprintf( '<%s> : %s', $msg, __METHOD__));

                }

              }

            }

          }

          if ( $notify_users) {
            foreach ($notify_users as $user) $notifyUser( '', $user);

          }

          Json::ack( $action);

        }

      } else { Json::nak( $action); }

    }
		elseif ( 'delete' == $action) {
			if ( ( $id = (int)$this->getPost('id')) > 0 ) {
				$dao = new dao\diary_events;
				$dao->delete( $id);

				Json::ack( $action);

			} else { Json::nak( $action); }

		}
		elseif ( 'diary-event-save' == $action) {
			$a = [
				'order' => str_pad( trim( (string)$this->getPost('order')), 3, ' ', STR_PAD_LEFT ),
				'event_name' => $this->getPost('event_name'),
				'event_type' => $this->getPost('event_type'),
				'icon' => $this->getPost('icon'),
				'comment_not_required' => (int)$this->getPost('comment_not_required'),
				'multi_day' => (int)$this->getPost('multi_day'),
				'prospective_seller' => (int)$this->getPost('prospective_seller'),
				'calendar' => (int)$this->getPost('calendar')

      ];

			$dao = new dao\diary_events;
      if ( $id = (int)$this->getPost('id')) {
        if ( $dto = $dao->getByID( $id)) {
          if ( $dto->system_event) {
            unset(
              $a['event_name'],
              $a['event_type'],
              $a['icon'],
              $a['prospective_seller'],
              $a['multi_day'],
              $a['comment_not_required']

            );

          }

          $dao->UpdateByID( $a, $id);
          Json::ack( $action);

        } else { Json::nak( $action); }

      }
      else {
        $dao->Insert( $a);
        Json::ack( $action);

      }

    }
    elseif ( 'get-feed' == $action) {
      /*
      ( _ => {
        _.post({
          url : _.url('event'),
          data : {
            action : 'get-feed',
            name : 'Sales'

          },

        }).then( d => console.log( d));

      }) (_brayworth_);
       */

      $name = $this->getPost('name');
      if ( in_array( $name, [ 'Sales'])) {
        $start = $this->getPost('start');
        if ( !$start || strtotime( $start) < 1) $start = date( 'Y-m-d', strtotime('-2 months'));

        $end = $this->getPost('end');
        if ( !$end || strtotime( $end) < 1) $end = date( 'Y-m-d', strtotime('+2 months'));

        if ( 'Sales' == $name) {
          $reader = dvc\cal\reader::JSONString( json_encode( $this->jCalendar( config::calendar_sales, $start, $end)));
          $feed = $reader->feed( $start, $end);

          Json::ack( $action)
            ->add( 'data', $feed);

        }

      } else { parent::postHandler(); }

    }
    elseif ( 'getevents' == $action) {
      /*
      ( _ => _.post({
        url : _.url('event'),
        data : {
          action : 'getevents',
          order : 'Sales, Rentals'
        },

      }).then( d => {
        if ( 'ack' == d.response) {
          console.table( d.data);

        }
        else {
          _.growl( d);

        }

      }))(_brayworth_);
      */

      $res = false;
      $dao = new dao\diary_events;
      $fields = 'event_name, appointment_inspection, comment_not_required, exclude_for_user';

      $_pref = [];
      if ( $preferedOrder = $this->getPost('order')) {
        $prefs = explode(',', $preferedOrder);

        $pri = 0;
        $_cal = [];
        foreach ($prefs as $pref) {
          $pref = trim( $pref);
          if ( isset( config::calendars[$pref])) {
            $_cal[] = sprintf( 'WHEN %d THEN %d', config::calendars[$pref], $pri++);

          }

        }

        if ( $_cal) {
          $_cal[] = sprintf( 'ELSE %d', $pri++);
          $_pref[] = sprintf( 'CASE `calendar` %s END ASC', implode( ' ', $_cal));

        }

      }

      $_pref[] = '`order` ASC';
      $_pref[] = '`event_name` ASC';

      // $dao->log = true;
      if ( $res = $dao->getAll( $fields, sprintf( 'ORDER BY %s', implode( ',', $_pref)))) {
        $a = [];
        foreach( $res->dtoSet() as $d) {
          if ( $hidden = dao\diary_events::isHidden( $d)) continue;

          $a[] = [
            'event' => $d->event_name,
            'appointment_inspection' => (int)$d->appointment_inspection,
            'comment_not_required' => (int)$d->comment_not_required

					];

        }

        Json::ack( $action)
          ->add( 'data', $a);

      }
      else {
        Json::nak( $action);

      }

    }
    elseif ( 'move' == $action) {
      if ( $id = (int)$this->getPost('id')) {
        $dao = new dao\diary_events;
        if ( $dto = $dao->getByID( $id)) {

          $v = 'up' == $this->getPost('direction') ? -5 : 5;
          $n = (int)$dto->order + (int)$v;

          if ( $n < 1) $n = '';

          $order = str_pad( trim( (string)$n), 3, ' ', STR_PAD_LEFT);
          $a = [ 'order' => $order ];

          $dao->UpdateByID( $a, $dto->id);

          \Json::ack( $action)
            ->add( 'order', $order);

        } else { \Json::nak( $action); }

      } else { \Json::nak( $action); }

    }
    elseif ( 'property-diary-get-by-id' == $action) {
      if ( $id = (int)$this->getPost('id')) {
        $dao = new dao\property_diary;
        if ( $dto = $dao->getByID( $id)) {

          $dto->address_street = '';
          $dto->people_name = '';
          if ( $dto->property_id) {
            $dao = new dao\properties;
            $dto->address_street = $dao->getFieldByID( $dto->property_id, 'address_street');

          }

          if ( $dto->people_id) {
            $dao = new dao\people;
            $dto->people_name = $dao->getFieldByID( $dto->people_id, 'name');

          }

          if ( !$dto->event_name) { // legacy
            $evt = trim( preg_replace( '@-.*@', '', $dto->subject));
            if ( $evt && dao\diary_events::isDiaryEvent( $evt)) {
              $dto->event_name = $evt;

            }

          }

          if ( strtotime( $dto->date_start) < 0) {
            $dto->date_start = $dto->date;

          }

          if ( !$dto->target_user && isset( $dto->item_user) && $dto->item_user) $dto->target_user = $dto->item_user;  // legacy
          if ( !$dto->attendants && isset( $dto->other_user_ids) && $dto->other_user_ids) { // legacy
            $a = [];
            $_a = json_decode( $dto->other_user_ids);
            foreach( $_a as $_ou) {
              if ( $_ou->user) {
                $a[] = $_ou->user;

              }

            }

            if ( $a) $dto->attendants = json_encode($a);

          }

          \Json::ack( $action)
            ->add( 'data', $dto);

        } else { \Json::nak( $action); }

      } else { \Json::nak( $action); }

    }
    elseif ( 'search-people' == $action) {
      if ( $term = $this->getPost('term')) {

				Json::ack( $action)
					->add( 'term', $term)
					->add( 'data', green\search::people( $term));

			} else { Json::nak( $action); }

    }
    elseif ( 'search-properties' == $action) {
			if ( $term = $this->getPost('term')) {
				Json::ack( $action)
					->add( 'term', $term)
					->add( 'data', green\search::properties( $term));

			} else { Json::nak( $action); }

    }
    elseif ( 'toggle-hide-event' == $action) {
      if ( $id = (int)$this->getPost('id')) {
        $dao = new dao\diary_events;
        if ( $dto = $dao->getByID( $id)) {
          $users = explode( ';', trim( $dto->exclude_for_user, '; '));
          $key = array_search( (int)currentUser::id(), $users);

          if ( $key === false) {
            \Json::ack( sprintf( '%s : hide', $action))->add( 'hidden', 1);
            $users[] = (int)currentUser::id();

          }
          else {
            \Json::ack( sprintf( '%s : show', $action))->add( 'hidden', 0);
            unset( $users[ $key]);

          }

          $a = [ 'exclude_for_user' => implode( ';', $users) . ';'];
          $dao->UpdateByID( $a, $dto->id);

        } else { \Json::nak( $action); }

      } else { \Json::nak( $action); }

    }
    else { parent::postHandler(); }

  }

  public function appointment() {
    $this->data = (object)[
      'title' => $this->title = 'New Appointment',
      'events' => [],
      'users' => [],

    ];


    $dao = new dao\diary_events;
    if ( $res = $dao->getAll( 'event_name, multi_day, comment_not_required, exclude_for_user')) {
      foreach( $res->dtoSet() as $d) {
        if ( $hidden = dao\diary_events::isHidden( $d)) continue;

        $this->data->events[] = (object)[
          'event' => $d->event_name,
          'multi_day' => (int)$d->multi_day,
          'comment_not_required' => (int)$d->comment_not_required

        ];

      }

    }

    $dao = new dao\users;
    if ( $res = $dao->getActive()) {
      $this->data->users = $res->dtoSet();

    }

    $this->load('appointment');

  }

	public function edit( $id = 0) {
		if ( $id = (int)$id) {
			$dao = new dao\diary_events;
			if ( $dto = $dao->getByID( $id)) {
				$this->data = (object)[
					'title' => $this->title = 'Edit Diary Event',
					'action' => 'update',
					'dto' => $dto

        ];

				$this->load('edit');

			}
			else {
				$this->load( 'not-found');

			}

		}
		else {
			$this->data = (object)[
				'action' => 'add',
				'title' => $this->title = 'Add Diary Event',
				'dto' => new dao\dto\diary_events

      ];

			$this->load('edit');

		}

	}

  public function js( $lib = '') {
    $s = [];
    $r = [];

    $s[] = '@{{route}}@';
    $r[] = strings::url( $this->route);

    $js = \file_get_contents( __DIR__ . '/js/custom.js');
    $js = preg_replace( $s, $r, $js);

    Response::javascript_headers();
    print $js;

  }

}