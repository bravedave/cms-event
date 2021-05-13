<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

namespace cms\event\dao\dto;

use dao\dto\_dto;

class diary_events extends _dto {
  public $id = 0;
  public $event_name = '';
  public $system_event = 0;
  public $prospective_seller = 0;
  public $multi_day = 0;
  public $appointment_inspection = 0;
  public $event_type = '';
  public $order = '';
  public $icon = '';
  public $exclude_for_user = '';
  public $calendar = 0;
  public $global = 0;

}