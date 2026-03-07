<?php

class Colosseum_Arena_Booking_Deactivator {

	public static function deactivate() {
		// Usually we do not drop tables on deactivation so users don't lose data.
		// If we wanted to, we could drop them here or provide an uninstall.php file.
	}

}
