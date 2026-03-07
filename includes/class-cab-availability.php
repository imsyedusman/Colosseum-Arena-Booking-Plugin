<?php

class CABA_Availability {

	public static function get_available_slots( $service_id, $date_str ) {
		global $wpdb;
		
		$service = CABA_DB::get_row( 'services', $service_id );
		if ( ! $service ) {
			return array();
		}

		$room_id = $service['room_id'];
		
		// Determine day type
		$day_of_week = date('w', strtotime($date_str));
		$day_type = ($day_of_week == 0 || $day_of_week == 6) ? 'weekends' : 'weekdays';

		// Get schedules for this service and day_type
		$table_schedules = CABA_DB::get_table_name( 'schedules' );
		$schedules = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_schedules WHERE service_id = %d AND day_type = %s ORDER BY start_time ASC", $service_id, $day_type ), ARRAY_A );

		if ( empty( $schedules ) ) {
			return array(); // No slots configured for this day
		}

		// Get existing bookings for the EXACT same room on that date (excluding cancelled)
		$table_bookings = CABA_DB::get_table_name( 'bookings' );
		
		// To completely prevent double bookings for Standard/Premium (which share Birthday Room),
		// we fetch ALL bookings in this room_id.
		$sql_bookings = $wpdb->prepare("
			SELECT b.start_time, b.end_time 
			FROM $table_bookings b
			LEFT JOIN " . CABA_DB::get_table_name('services') . " s ON b.service_id = s.id
			WHERE s.room_id = %d 
			AND b.booking_date = %s 
			AND b.status != 'cancelled'
		", $room_id, $date_str);
		
		$room_bookings = $wpdb->get_results( $sql_bookings, ARRAY_A );

		$available_slots = array();

		foreach ( $schedules as $sch ) {
			$slot_start = strtotime( $date_str . ' ' . $sch['start_time'] );
			$slot_end   = strtotime( $date_str . ' ' . $sch['end_time'] );
			
			// Don't show slots in the past if it's today
			if ( $date_str == current_time('Y-m-d') && $slot_start <= current_time('timestamp') ) {
				continue;
			}

			$is_overlapping = false;
			
			foreach ( $room_bookings as $bk ) {
				$bk_start = strtotime( $date_str . ' ' . $bk['start_time'] );
				$bk_end   = strtotime( $date_str . ' ' . $bk['end_time'] );

				// Overlap condition: (StartA < EndB) and (EndA > StartB)
				if ( $slot_start < $bk_end && $slot_end > $bk_start ) {
					$is_overlapping = true;
					break;
				}
			}

			if ( ! $is_overlapping ) {
				$available_slots[] = array(
					'start_time' => substr( $sch['start_time'], 0, 5 ),
					'end_time'   => substr( $sch['end_time'], 0, 5 ),
					'label'      => substr( $sch['start_time'], 0, 5 ) . ' - ' . substr( $sch['end_time'], 0, 5 )
				);
			}
		}

		return $available_slots;
	}
}
