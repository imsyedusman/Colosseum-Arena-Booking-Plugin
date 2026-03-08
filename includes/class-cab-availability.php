<?php

class CABA_Availability {

	public static function get_available_slots( $service_id, $date_str ) {
		global $wpdb;
		
		$service = CABA_DB::get_row( 'services', $service_id );
		if ( ! $service ) {
			return array();
		}

		$room_id = $service['room_id'];
		
		// Determine day type variables
		$day_of_week = date('w', strtotime($date_str)); // 0=Sun, 1=Mon...
		$is_weekend = ($day_of_week == 0 || $day_of_week == 6);
        $day_name = strtolower(date('l', strtotime($date_str))); // monday, tuesday...

		// Get all schedules
		$table_schedules = CABA_DB::get_table_name( 'schedules' );
		$all_schedules = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_schedules WHERE service_id = %d ORDER BY start_time ASC", $service_id ), ARRAY_A );

        // Filter valid schedules for THIS specific date
		$schedules = array();
        foreach($all_schedules as $s) {
            $dt = $s['day_type'];
            if ($dt === 'daily') $schedules[] = $s;
            else if ($dt === 'weekdays' && !$is_weekend) $schedules[] = $s;
            else if ($dt === 'weekends' && $is_weekend) $schedules[] = $s;
            else if ($dt === $day_name) $schedules[] = $s;
        }

		if ( empty( $schedules ) ) {
			return array(); // No slots configured for this day
		}

		$room_bookings = array();
		if ( $room_id > 0 ) {
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
				AND b.status != 'expired'
			", $room_id, $date_str);
			
			$room_bookings = $wpdb->get_results( $sql_bookings, ARRAY_A );
		}

		$duration_mins = intval($service['duration']);
		if ($duration_mins <= 0) $duration_mins = 60; // fallback safety
        
		$available_slots = array();

		foreach ( $schedules as $sch ) {
			$window_start = strtotime( $date_str . ' ' . $sch['start_time'] );
			$window_end   = strtotime( $date_str . ' ' . $sch['end_time'] );
			
			// Generate slots of $duration_mins inside this schedule window
			for ($slot_start = $window_start; $slot_start + ($duration_mins * 60) <= $window_end; $slot_start += ($duration_mins * 60)) {
				$slot_end = $slot_start + ($duration_mins * 60);

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
						'start_time' => date('H:i', $slot_start),
						'end_time'   => date('H:i', $slot_end),
						'label'      => date('H:i', $slot_start) . ' - ' . date('H:i', $slot_end)
					);
				}
			}
		}

		return $available_slots;
	}
}
