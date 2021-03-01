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

use green;
use Json;
use Response;
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
    else {
      parent::postHandler();

    }

  }

  function appointment() {
    $this->data = (object)[
      'title' => $this->title = 'New Event',
      'events' => [],
      'users' => [],

    ];


    $dao = new dao\diary_events;
    if ( $res = $dao->getAll( 'event_name, appointment_inspection, comment_not_required, exclude_for_user')) {
      foreach( $res->dtoSet() as $d) {
        if ( $hidden = dao\diary_events::isHidden( $d)) continue;

        $this->data->events[] = (object)[
          'event' => $d->event_name,
          'appointment_inspection' => (int)$d->appointment_inspection,
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