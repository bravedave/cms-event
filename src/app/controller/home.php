<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

class home extends dvc\cal\controller {
  const colors = [
		[ 'back' => '#8ec3eb', 'fore' => '#000'],

  ];

	protected function before() {
    parent::before();

    $this->feeds = [];
    $i = 0;

    array_unshift( $this->feeds, (object)[
      'name' => 'Sales',
      'url' => strings::url( 'event'),
      'data' => json_encode((object)['type' => 'sales-calendar']),
      'forecolor' =>  self::colors[ $i % count( self::colors)]['fore'],
      'color' =>  self::colors[ $i++ % count( self::colors)]['back'],
      'method' => 'POST'

    ]);

  }

  protected function page( $params) {
		$defaults = [
			'scripts' => [],

		];

    $options = array_merge( $defaults, $params);

    $options['scripts'][] = sprintf(
      '<script type="text/javascript" src="%s"></script>',
      strings::url('js/event')

    );

    return parent::page( $options);

  }

}
