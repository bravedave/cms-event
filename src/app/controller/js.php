<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

use dvc\jslib;

class js extends Controller {
	public function event() {
    jslib::viewjs([
      'debug' => false,
      'libName' => 'cms-event',
      'jsFiles' => sprintf( '%s/app/js/*.js', $this->rootPath ),
      'libFile' => config::tempdir()  . '_event_tmp.js'

    ]);

  }

}
