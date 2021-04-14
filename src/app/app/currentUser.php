<?php
/*
 * David Bray
 * BrayWorth Pty Ltd
 * e. david@brayworth.com.au
 *
 * MIT License
 *
*/

use dvc\imap\account;

abstract class currentUser extends dvc\currentUser {
  static public function email() {
    return account::$EMAIL;

  }

  public static function FirstName() : string {
    return strings::FirstWord( account::$NAME);

  }

  static function name() {
    return account::$NAME;

	}

	static public function mailer() {
		/*
		 *	Return the appropriate PHP-Mailer object
		 */
		$mail = \sys::mailer();
		$mail->From     = currentUser::email();
		$mail->FromName = currentUser::name();
		$mail->Sender	= $mail->From;

		$mail->isSMTP(); // use smtp with server set to mail

		if ( account::$SMTP_SERVER) {
			$mail->Host = account::$SMTP_SERVER;

		}

		if ( account::$SMTP_PORT) {
			$mail->Port = account::$SMTP_PORT;
      // if ( 587 == account::$SMTP_PORT) $mail->SMTPSecure = 'tls';

		}

    // \sys::logger( sprintf('<%s:%s/%s> %s', $mail->Host, $mail->Port, $mail->SMTPSecure, __METHOD__));


		if ( account::$USERNAME && account::$PASSWORD) {
			$mail->SMTPAuth = true;
			$mail->Username = account::$SMTP_USERNAME;
			$mail->Password = account::$SMTP_PASSWORD;

		}

		return ( $mail);

	}

}
