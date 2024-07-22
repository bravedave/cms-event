<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * This work is licensed under a Creative Commons Attribution 4.0 International Public License.
 *      http://creativecommons.org/licenses/by/4.0/
 *
*/

namespace dvc\mail;

use cms;

abstract class config {
	static $ENABLED = '';
	static $MODE = '';

	static function config() {
		return cms\config::dataPath() . DIRECTORY_SEPARATOR . 'mail-config.json';
	}

	static function init() {
		if (!class_exists('dvc\ews\config')) {
			self::$MODE = 'imap';
		}

		if (file_exists($config = self::config())) {
			$j = json_decode(file_get_contents($config));

			if (isset($j->mode)) self::$MODE = $j->mode;
		}

		self::$ENABLED = ((bool)self::$MODE);
	}
}

config::init();
