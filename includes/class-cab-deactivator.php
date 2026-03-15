<?php

class Colosseum_Arena_Booking_Deactivator {

	public static function deactivate() {
		$timestamp = wp_next_scheduled( 'cab_expire_pending_bookings_cron' );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, 'cab_expire_pending_bookings_cron' );
		}
	}

}
