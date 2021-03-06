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
use green;

class property_diary extends green\property_diary\dao\property_diary {
  public function getCalendar( int $iCal, string $start, string $end) : ?array {

    $dao = new diary_events;
    $events = [];
    if ( config::calendar_sales == $iCal) {
      $events = ['Appointment'];

    }
    else {
      // I don't think this is ever used
      throw new \Exception('I don\'t think this is ever used');
      $events = $dao->getCalendarEvents( $iCal, $htmlSafe = true);

    }

    if ( $events) {
      // \sys::logger( sprintf('<%s> %s', '1', __METHOD__));

      $fields = [
        '`pd`.`id`',
        '`pd`.`date`',
        '`pd`.`date_start`',
        '`pd`.`date_end`',
        '`pd`.`event`',
        '`pd`.`subject`',
        '`pd`.`location`',
        '`pd`.`attendants`',
        '`pd`.`target_user`',

      ];

      if ( $this->db->field_exists('property_diary', 'item_user')) {
        $fields[] = '`pd`.`item_user`';

      }

      if ( $this->db->field_exists('property_diary', 'other_user_ids')) {
        // \sys::logger( sprintf('<%s> %s', 'with other_user_ids', __METHOD__));
        $fields[] = '`pd`.`other_user_ids`';

      }

      $sql = sprintf(
        'SELECT
          %s
        FROM
          `property_diary` `pd`
        WHERE
          `pd`.`event` IN ("%s") AND DATE( `pd`.`date`) BETWEEN "%s" AND "%s"',
        implode( ',', $fields),
        implode( '","', $events),
        $start,
        $end

      );

      // \sys::logSQL( sprintf('<%s> %s', $sql, __METHOD__));

      if ( $res = $this->Result( $sql)) {
        $dao = new users;
        return $res->dtoSet( function( $dto) use ($dao) {
          $o = [];
          $os = [];

          if ( $dto->target_user) {
            $o[] = $x = (object)[
              'id' => $dto->target_user,
              'name' => $dao->getFieldByID( $dto->target_user, 'name')

            ];

            $os[] = \strings::Initials( $x->name);

          }

          if ( $dto->attendants) {
            $_a = (array)json_decode( $dto->attendants);
            foreach( $_a as $_u) {
              if ( $_u) {
                $o[] = $x = (object)[
                  'id' => $_u,
                  'name' => $dao->getFieldByID( $_u, 'name')

                ];

                $os[] = \strings::Initials( $x->name);

              }

            }

          }

          if ( isset( $dto->item_user)) { /** legacy cms field */
            if ( $dto->item_user) {
              $o[] = $x = (object)[
                'id' => $dto->item_user,
                'name' => $dao->getFieldByID( $dto->item_user, 'name')

              ];

              $os[] = \strings::Initials( $x->name);

            }

          }

          if ( isset( $dto->other_user_ids)) { /** legacy cms field */

            // \sys::logger( sprintf('<%s> %s', 'with other_user_ids', __METHOD__));

            if ( $dto->other_user_ids) {
              $_a = json_decode( $dto->other_user_ids);
              foreach( $_a as $_ou) {
                if ( $_ou->user) {
                  $o[] = $x = (object)[
                    'id' => $_ou->user,
                    'name' => $dao->getFieldByID( $_ou->user, 'name')

                  ];

                  $os[] = \strings::Initials( $x->name);
                  break;

                }

              }

            }

          }

          if ( $os) $dto->subject .= ' - ' . implode( '|', $os);
          unset( $dto->item_user);
          unset( $dto->other_user_ids);

          return $dto;

        });

      }

    }

    return null;

  }

}
