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
use strings;

class controller extends \Controller {
  protected $viewPath = __DIR__ . '/views/';

  protected function _index() {

    $dao = new dao\diary_events;
    $this->data = (object)[
      'dto' => false,
      'dataset' => $dao->getAll()

    ];

    $secondary = ['index'];

    if ( config::$_CMS_EVENT_DEVELOPER) {
      $secondary[] = 'index-developer';

    }

		$this->render([
			'title' => $this->title = 'Diary Events',
			'primary' => 'list',
			'secondary' => $secondary

    ]);

  }

  protected function postHandler() {
    $action = $this->getPost('action');
    if ( 'getevents' == $action) {
      $dao = new dao\diary_events;
      if ( $res = $dao->getAll( 'event_name, appointment_inspection, comment_not_required, exclude_for_user')) {
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
    else {
      parent::postHandler();

    }

  }

  function appointment() {
    $dao = new dao\diary_events;
    $this->data = (object)[
      'title' => $this->title = 'New Event',
      'diaryEvents' => $dao->getDiaryEvents()

    ];

    $this->load('appointment');

  }

}