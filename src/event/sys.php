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

use strings;

class sys extends \sys {
	static function IconForEvent( string $event) : string {

		if ( preg_match( '/(buy enq|prop enq)/i', $event )) {
			return strings::imageInline( __DIR__ . '/images/icon-buyerenquiry.svg');

		}

		if ( preg_match( '/info/i', $event )) {
			return strings::imageInline( __DIR__ . '/images/icon-info.svg');

		}

		return parent::IconForEvent( $event);

	}

}