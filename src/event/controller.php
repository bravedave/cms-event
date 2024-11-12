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

use cms\{currentUser, strings};
use bravedave\dvc\{json, logger, Response};
use cms, green;
use dvc;

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
      'dataset' => $dao->getAllwithInactive()

    ];

    $secondary = ['index'];

    // if (config::$_CMS_EVENT_DEVELOPER) {
    //   // $secondary[] = 'index-developer';

    // }

    $this->render([
      'title' => $this->title = 'Diary Events',
      'primary' => 'list',
      'secondary' => $secondary,
      'data' => (object)[
        'pageUrl' => strings::url($this->route)

      ]

    ]);
  }

  protected function jCalendar($iCal, $start, $end): array {
    $debug = false;
    $ret = [];

    $autotime = '07:00:00';

    $dao = new dao\property_diary;
    if ($dtoSet = $dao->getCalendar($iCal, $start, $end)) {
      // sys::dump( $dtoSet);

      $filter = function ($dto) {
        return true;
      };
      if ($this->jCalendarFilter) {
        $filter = $this->jCalendarFilter;
      }

      foreach ($dtoSet as $dto) {

        if (!$filter($dto)) continue;

        $start = new \DateTime($dto->date_start == '0000-00-00 00:00:00' ? $dto->date . ' ' . $autotime : $dto->date_start);
        $end = new \DateTime($dto->date_end == '0000-00-00 00:00:00' ? $dto->date . ' ' . $autotime : $dto->date_end);
        $diff = $end->getTimestamp() - $start->getTimestamp();

        $ret[] = [
          'title' => $dto->subject,
          'location' => $dto->location,
          'href' => $dto->href,
          'notes' => '',
          'start' => $start->format('c'),
          'end' => $diff < 1801 ? $start->format('c') : $end->format('c'),
          'id' => sprintf('property-diary-%d@cms-event', $dto->id),
          'allDay' => (int)('00:00' == $start->format('HH:mm') && '23:59' == $end->format('HH:mm')),
          'changekey' => '',
          'src' => 'property_diary'
        ];
      }
    }

    return $ret;
  }

  protected function postHandler() {
    $action = $this->getPost('action');
    if ('appointment-delete' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\property_diary;
        $dao->delete($id);
        json::ack($action);
      } else {
        json::nak($action);
      }
    } elseif ('appointment-post' == $action) {

      $multiday = (int)$this->getPost('multiday');
      if ($multiday) {

        $start = sprintf('%s 00:00:00', $this->getPost('date'));
        $end = sprintf('%s 23:59:59', $this->getPost('date_end'));
      } else {

        $start_time = $this->getPost('start');
        $start = sprintf('%s %s', $this->getPost('date'), $start_time);
        $end = sprintf('%s %s', $this->getPost('date'), $this->getPost('end'));
      }

      if (strtotime($start) > 0 && strtotime($end) > strtotime($start)) {
        $a = [
          'date' => $this->getPost('date'),
          'date_start' => date('Y-m-d H:i:s', strtotime($start)),
          'date_end' => date('Y-m-d H:i:s', strtotime($end)),
          'event' => 'Appointment',
          'event_name' => $this->getPost('event'),
          'property_id' => $this->getPost('property_id'),
          'people_id' => $this->getPost('people_id'),
          'location' => $this->getPost('location'),
          'comments' => $this->getPost('notes'),
          'href' => $this->getPost('href'),
          'target_user' => $this->getPost('target_user'),
          'user_id' => currentUser::id(),
          'updated' => \db::dbTimeStamp(),
          'update_user_id' => currentUser::id(),
          'attendants' => '',
          'notify_users' => '',
          'notify_message' => $this->getPost('notify_message'),
          'notify_reminder' => $this->getPost('notify_reminder'),
        ];

        if ($attendants = $this->getPost('attendants')) {
          $a['attendants'] = json_encode((array)$attendants);
        }

        if ($notify_users = $this->getPost('notify_users')) {
          $a['notify_users'] = json_encode((array)$notify_users);
        }

        // subject is activity - property - person

        $street = $this->getPost('address_street');
        $location = $this->getPost('location');

        $a['location'] = $location ? $location : $street;

        $name = $this->getPost('people_name');
        $_subject = [];
        if ($a['event_name']) $_subject[] = $a['event_name'];
        if ($street) $_subject[] = strings::GoodStreetString($street);
        if ($name) $_subject[] = $name;
        if ($location) $_subject[] = sprintf('loc:%s', $location);

        $a['subject'] = implode(' - ', $_subject);

        $notifyUser = function ($suffix, $uid) use ($a): bool {

          $dao = new dao\users;
          if ($uDto = $dao->getByID($uid)) {

            $start = date('D d/m - g:ia', strtotime($a['date_start']));

            $notes = [
              '<span style="font-family: monospace;">',
              '<strong>Appointment Details</strong>',
              '',
              'Date/time ..: ' . $start,
              'Activity ...: ' . $a['event_name']

            ];

            if ($person = $this->getPost('people_name')) $notes[] = 'Person .....: ' . $person;
            if ($a['location']) $notes[] = 'Location ...: ' . $a['location'];
            if ($address = $this->getPost('address_street')) $notes[] = 'Property ...: ' . $address;
            if ($a['comments']) $notes[] = sprintf('Notes ......:<blockquote style="margin-left: 1em;">%s</blockquote>', strings::text2html($a['comments']));
            if ($a['notify_message']) {
              $notes[] = '';
              $notes[] = sprintf('Instructions:<blockquote style="margin-left: 1em;">%s</blockquote>', strings::text2html($a['notify_message']));
            }

            $signoff = currentUser::user()->name;
            if (isset(currentUser::user()->email_signoff) && currentUser::user()->email_signoff)
              $signoff = currentUser::user()->email_signoff;

            $notes[] = '';
            $notes[] = $signoff;
            $notes[] = '</span>';

            $msgHtml = implode('<br>', $notes);

            if (class_exists('emailTemplate')) {
              /**
               * wrap the message in our customized templates
               **/
              $eBody = new \emailTemplate;
              $eBody->BuildContactInfoFromUser();
              $eBody->messageSpace = $msgHtml;
              $msgHtml = $eBody->renderInline();
            }

            $mail = currentUser::mailer();
            $mail->Subject = sprintf('Appt.%s %s - %s', $suffix, $a['event_name'], $start);
            $mail->msgHTML($msgHtml);
            $mail->AddAddress($uDto->email, $uDto->name);
            if ($mail->send()) {

              return true;
            } else {

              logger::info(sprintf('<failed to send notification> <%s> %s', $mail->ErrorInfo, __METHOD__));
            }
          } else {

            logger::info(sprintf('<failed to find user to notify>  %s', __METHOD__));
          }

          return false;
        };

        $dao = new dao\property_diary;
        if ($id = (int)$this->getPost('id')) {

          $dao->UpdateByID($a, $id);
          json::ack($action);

          if ($notify_users) {

            $notified = [];
            foreach ($notify_users as $user) {

              if ($notifyUser('(u)', $user)) {

                $notified[] = ['id' => $user, 'date' => date('Y-m-d H:i:s')];
              }
            }

            if ($notified) {

              if ($pdDTO = $dao->getByID($id)) {

                // append $notified to the end of $pdDTO->notify_users_sent;
                $notify_users_sent = $pdDTO->notify_users_sent ? json_decode($pdDTO->notify_users_sent) : [];
                foreach ($notified as $n) $notify_users_sent[] = $n;
                $dao->UpdateByID(['notify_users_sent' => json_encode($notify_users_sent)], $id);
              }
            }
          }
        } else {

          $a['created'] = $a['updated'];
          $id = $dao->Insert($a);
          if ((int)$a['target_user'] && (int)$a['target_user'] != currentUser::id()) {

            if ('yes' == $this->getPost('notify_target_user')) {

              $dao = new dao\users;
              if ($u = $dao->getByID($a['target_user'])) {

                $msg = sprintf(
                  "I have booked a %s for us on %s at %s %s%s - %s",
                  $a['event'],
                  strings::asLongDate($a['date']),
                  $start_time,
                  $name ? sprintf('with %s ', $name) : '',
                  $a['location'] ? sprintf('at %s ', $a['location']) : '',
                  currentUser::FirstName()
                );

                if (class_exists('\cms\sms')) {

                  cms\sms::notifyUser($a['target_user'], $msg);
                } else {

                  logger::info(sprintf('<\cms\sms - class not found> : %s', __METHOD__));
                  logger::info(sprintf('<%s> : %s', $msg, __METHOD__));
                }
              }
            }
          }

          if ($notify_users) {

            $notified = [];
            foreach ($notify_users as $user) {

              if ($notifyUser('', $user)) {

                $notified[] = ['id' => $user, 'date' => date('Y-m-d H:i:s')];
              }
            }

            if ($notified) {

              $dao->UpdateByID(['notify_users_sent' => json_encode($notified)], $id);
            }
          }

          json::ack($action);
        }
      } else json::nak($action);
    } elseif ('delete' == $action) {
      if (($id = (int)$this->getPost('id')) > 0) {
        $dao = new dao\diary_events;
        $dao->delete($id);

        json::ack($action);
      } else {
        json::nak($action);
      }
    } elseif ('diary-event-save' == $action) {

      $a = [
        'order' => str_pad(trim((string)$this->getPost('order')), 3, ' ', STR_PAD_LEFT),
        'event_name' => $this->getPost('event_name'),
        'event_type' => $this->getPost('event_type'),
        'icon' => $this->getPost('icon'),
        'multi_day' => (int)$this->getPost('multi_day'),
        'prospective_seller' => (int)$this->getPost('prospective_seller'),
        'calendar' => (int)$this->getPost('calendar'),
        'global' => (int)$this->getPost('global')
      ];

      $dao = new dao\diary_events;
      if ($id = (int)$this->getPost('id')) {

        if ($dto = $dao->getByID($id)) {

          if ($dto->system_event) {
            unset(
              $a['event_name'],
              $a['event_type'],
              $a['icon'],
              $a['prospective_seller'],
              $a['multi_day']
            );
          }

          $dao->UpdateByID($a, $id);
          json::ack($action);
        } else {

          json::nak($action);
        }
      } else {

        $dao->Insert($a);
        json::ack($action);
      }
    } elseif ('get-feed' == $action) {
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
      if (in_array($name, ['Sales'])) {
        $start = $this->getPost('start');
        if (!$start || strtotime($start) < 1) $start = date('Y-m-d', strtotime('-2 months'));

        $end = $this->getPost('end');
        if (!$end || strtotime($end) < 1) $end = date('Y-m-d', strtotime('+2 months'));

        if ('Sales' == $name) {
          $reader = dvc\cal\reader::JSONString(json_encode($this->jCalendar(config::calendar_sales, $start, $end)));
          $feed = $reader->feed($start, $end);

          json::ack($action)
            ->add('data', $feed);
        }
      } else {
        parent::postHandler();
      }
    } elseif ('get-reminders' == $action) {
      /*
      ( _ => _.post({
          url : _.url('event'),
          data : {
            action : 'get-reminders'
          },

        }).then( d => 'ack' == d.response ? console.log( d.data) : _.growl( d))

      )(_brayworth_);
      */
      $dao = new dao\property_diary;
      json::ack($action)
        ->add('data', $dao->getReminders(currentUser::id()));
    } elseif ('getevents' == $action) {
      /*
      ( _ => _.post({
          url : _.url('event'),
          data : {
            action : 'getevents',
            order : 'Sales, Rentals'
          },

        }).then( d => 'ack' == d.response ? console.table( d.data) : _.growl( d))

      )(_brayworth_);
      */

      $res = false;
      $dao = new dao\diary_events;
      $fields = 'event_name, appointment_inspection, exclude_for_user';

      // $dao->log = true;
      if ($res = $dao->getAll($fields)) {
        $a = [];
        foreach ($res->dtoSet() as $d) {
          if ($hidden = dao\diary_events::isHidden($d)) continue;

          $a[] = [
            'event' => $d->event_name,
            'appointment_inspection' => (int)$d->appointment_inspection

          ];
        }

        json::ack($action)
          ->add('data', $a);
      } else {
        json::nak($action);
      }
    } elseif (in_array($action, ['mark-inactive', 'mark-inactive-undo'])) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\diary_events;
        if ($dto = $dao->getByID($id)) {

          $a = ['inactive' => 'mark-inactive' == $action ? 1 : 0];

          $dao->UpdateByID($a, $dto->id);

          json::ack($action)
            ->add('inactive', 'mark-inactive' == $action ? 1 : 0);
        } else {
          json::nak($action);
        }
      } else {
        json::nak($action);
      }
    } elseif ('move' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\diary_events;
        if ($dto = $dao->getByID($id)) {

          $v = 'up' == $this->getPost('direction') ? -5 : 5;
          $n = (int)$dto->order + (int)$v;

          if ($n < 1) $n = '';

          $order = str_pad(trim((string)$n), 3, ' ', STR_PAD_LEFT);
          $a = ['order' => $order];

          $dao->UpdateByID($a, $dto->id);

          json::ack($action)
            ->add('order', $order);
        } else {
          json::nak($action);
        }
      } else {
        json::nak($action);
      }
    } elseif ('reminder-dismiss' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\property_diary;
        $dao->UpdateByID(['notify_reminder' => config::notify_reminder_dismissed], $id);
      }

      json::ack($action);
    } elseif ('property-diary-get-by-id' == $action) {

      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\property_diary;
        if ($dto = $dao->getByID($id)) {

          $dto->address_street = '';
          $dto->people_name = '';

          if ($dto->property_id) $dto->address_street = (new dao\properties)->getFieldByID($dto->property_id, 'address_street');
          if ($dto->people_id) $dto->people_name = (new dao\people)->getFieldByID($dto->people_id, 'name');

          if (!$dto->event_name) { // legacy

            $evt = trim(preg_replace('@-.*@', '', $dto->subject));
            if ($evt && dao\diary_events::isDiaryEvent($evt)) $dto->event_name = $evt;
          }

          if (strtotime($dto->date_start) < 0) $dto->date_start = $dto->date;

          if (!$dto->target_user && isset($dto->item_user) && $dto->item_user) $dto->target_user = $dto->item_user;  // legacy
          if (!$dto->attendants && isset($dto->other_user_ids) && $dto->other_user_ids) { // legacy

            $a = [];
            $_a = json_decode($dto->other_user_ids);
            foreach ($_a as $_ou) {

              if ($_ou->user) $a[] = $_ou->user;
            }

            if ($a) $dto->attendants = json_encode($a);
          }

          json::ack($action)
            ->add('data', $dto);
        } else {

          json::nak($action);
        }
      } else {

        json::nak($action);
      }
    } elseif ('search-people' == $action) {
      if ($term = $this->getPost('term')) {

        json::ack($action)
          ->add('term', $term)
          ->add('data', green\search::people($term));
      } else {
        json::nak($action);
      }
    } elseif ('search-properties' == $action) {
      if ($term = $this->getPost('term')) {
        json::ack($action)
          ->add('term', $term)
          ->add('data', green\search::properties($term));
      } else {
        json::nak($action);
      }
    } elseif ('toggle-hide-event' == $action) {
      if ($id = (int)$this->getPost('id')) {
        $dao = new dao\diary_events;
        if ($dto = $dao->getByID($id)) {
          $users = explode(';', trim($dto->exclude_for_user, '; '));
          $key = array_search((int)currentUser::id(), $users);

          if ($key === false) {
            json::ack(sprintf('%s : hide', $action))->add('hidden', 1);
            $users[] = (int)currentUser::id();
          } else {
            json::ack(sprintf('%s : show', $action))->add('hidden', 0);
            unset($users[$key]);
          }

          $a = ['exclude_for_user' => implode(';', $users) . ';'];
          $dao->UpdateByID($a, $dto->id);
        } else {
          json::nak($action);
        }
      } else {
        json::nak($action);
      }
    } else {
      parent::postHandler();
    }
  }

  public function appointment() {

    $this->data = (object)[
      'title' => $this->title = 'New Appointment',
      'events' => [],
      'users' => (new dao\users)->getActive(),
    ];

    if ($res = (new dao\diary_events)->getAll('event_name, multi_day, exclude_for_user')) {

      foreach ($res->dtoSet() as $d) {
        if ($hidden = dao\diary_events::isHidden($d)) continue;

        $this->data->events[] = (object)[
          'event' => $d->event_name,
          'multi_day' => (int)$d->multi_day
        ];
      }
    }

    $this->load('appointment');
  }

  public function edit($id = 0) {

    if ($id = (int)$id) {

      if ($dto = (new dao\diary_events)->getByID($id)) {
        $this->data = (object)[
          'title' => $this->title = 'Edit Diary Event',
          'action' => 'update',
          'dto' => $dto

        ];

        $this->load('edit');
      } else {

        $this->load('not-found');
      }
    } else {

      $this->data = (object)[
        'action' => 'add',
        'title' => $this->title = 'Add Diary Event',
        'dto' => new dao\dto\diary_events
      ];

      $this->load('edit');
    }
  }

  public function js($lib = '') {

    $s = [];
    $r = [];

    $s[] = '@{{route}}@';
    $r[] = strings::url($this->route);

    $js = \file_get_contents(__DIR__ . '/js/custom.js');
    $js = preg_replace($s, $r, $js);

    Response::javascript_headers();
    print $js;
  }
}
