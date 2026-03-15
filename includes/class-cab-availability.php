<?php

class CABA_Availability {

	public static function get_available_slots( $service_id, $date_str, $custom_duration = 0 ) {
		global $wpdb;

		$service = CABA_DB::get_row( 'services', $service_id );
		if ( ! $service ) {
			return array();
		}

		$room_id = intval( $service['room_id'] );
		$day_of_week = date( 'w', strtotime( $date_str ) );
		$is_weekend = ( 0 === intval( $day_of_week ) || 6 === intval( $day_of_week ) );
		$day_name = strtolower( date( 'l', strtotime( $date_str ) ) );
		$normalized_day_name = self::normalize_day_type( $day_name );

		$table_schedules = CABA_DB::get_table_name( 'schedules' );
		$all_schedules = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table_schedules WHERE service_id = %d ORDER BY start_time ASC",
				$service_id
			),
			ARRAY_A
		);

		$schedules = array();
		foreach ( $all_schedules as $schedule ) {
			$normalized_day_type = self::normalize_day_type( $schedule['day_type'] );
			$schedule['day_type_normalized'] = $normalized_day_type;
			if ( 'daily' === $normalized_day_type ) {
				$schedules[] = $schedule;
			} elseif ( 'weekdays' === $normalized_day_type && ! $is_weekend ) {
				$schedules[] = $schedule;
			} elseif ( 'weekends' === $normalized_day_type && $is_weekend ) {
				$schedules[] = $schedule;
			} elseif ( $normalized_day_type === $normalized_day_name ) {
				$schedules[] = $schedule;
			}
		}

		$duration_mins = ( $custom_duration > 0 ) ? intval( $custom_duration ) : intval( $service['duration'] );
		if ( $duration_mins <= 0 ) {
			$duration_mins = 60;
		}

		if ( empty( $schedules ) ) {
			error_log( 'SERVICE ID: ' . $service_id );
			error_log( 'DURATION: ' . $duration_mins );
			error_log( 'INTERVALS: ' . wp_json_encode( array() ) );
			error_log( 'SLOTS GENERATED: 0' );
			return array();
		}

		$room_bookings = self::get_room_bookings( $room_id, $date_str );

		$available_slots = array();
		$slot_keys = array();
		$today = current_time( 'Y-m-d' );
		$now_ts = current_time( 'timestamp' );

		foreach ( $schedules as $schedule ) {
			$window_start = self::build_timestamp( $date_str, $schedule['start_time'] );
			$window_end = self::build_timestamp( $date_str, $schedule['end_time'] );
			if ( ! $window_start || ! $window_end || $window_end <= $window_start ) {
				continue;
			}

			$breaks = self::parse_breaks( $schedule['breaks'], $date_str );

			if ( $room_id > 0 ) {
				if ( $date_str === $today && $window_start <= $now_ts ) {
					continue;
				}

				if ( self::overlaps_any_interval( $window_start, $window_end, $breaks ) ) {
					continue;
				}

				if ( self::overlaps_any_booking( $window_start, $window_end, $room_bookings, $date_str ) ) {
					continue;
				}

				self::append_slot( $available_slots, $slot_keys, $window_start, $window_end );
				continue;
			}

			for ( $slot_start = $window_start; $slot_start + ( $duration_mins * 60 ) <= $window_end; $slot_start += ( $duration_mins * 60 ) ) {
				$slot_end = $slot_start + ( $duration_mins * 60 );

				if ( $date_str === $today && $slot_start <= $now_ts ) {
					continue;
				}

				if ( self::overlaps_any_interval( $slot_start, $slot_end, $breaks ) ) {
					continue;
				}

				self::append_slot( $available_slots, $slot_keys, $slot_start, $slot_end );
			}
		}

		$availability_intervals = array_map(
			function ( $schedule ) {
				return array(
					'day_type' => isset( $schedule['day_type'] ) ? $schedule['day_type'] : '',
					'day_type_normalized' => isset( $schedule['day_type_normalized'] ) ? $schedule['day_type_normalized'] : '',
					'start_time' => isset( $schedule['start_time'] ) ? $schedule['start_time'] : '',
					'end_time' => isset( $schedule['end_time'] ) ? $schedule['end_time'] : '',
					'breaks' => isset( $schedule['breaks'] ) ? $schedule['breaks'] : '',
				);
			},
			$schedules
		);

		error_log( 'SERVICE ID: ' . $service_id );
		error_log( 'DURATION: ' . $duration_mins );
		error_log( 'INTERVALS: ' . wp_json_encode( $availability_intervals ) );
		error_log( 'SLOTS GENERATED: ' . count( $available_slots ) );

		if ( empty( $available_slots ) ) {
			error_log(
				sprintf(
					'CAB Availability: No slots generated for Service ID %d (%s) on date %s. Duration: %d min. Schedules count: %d.',
					$service_id,
					$service['name'],
					$date_str,
					$duration_mins,
					count( $schedules )
				)
			);
		}

		return $available_slots;
	}

	private static function append_slot( &$available_slots, &$slot_keys, $slot_start, $slot_end ) {
		$slot_key = date( 'H:i', $slot_start ) . '_' . date( 'H:i', $slot_end );
		if ( isset( $slot_keys[ $slot_key ] ) ) {
			return;
		}

		$slot_keys[ $slot_key ] = true;
		$available_slots[] = array(
			'start_time' => date( 'H:i', $slot_start ),
			'end_time'   => date( 'H:i', $slot_end ),
			'label'      => date( 'H:i', $slot_start ) . ' - ' . date( 'H:i', $slot_end ),
		);
	}

	private static function get_room_bookings( $room_id, $date_str ) {
		global $wpdb;

		if ( $room_id <= 0 ) {
			return array();
		}

		$table_bookings = CABA_DB::get_table_name( 'bookings' );
		$table_services = CABA_DB::get_table_name( 'services' );

		$sql = $wpdb->prepare(
			"SELECT b.start_time, b.end_time
			FROM $table_bookings b
			LEFT JOIN $table_services s ON b.service_id = s.id
			WHERE s.room_id = %d
			AND b.booking_date = %s
			AND b.status NOT IN ('cancelled', 'expired')",
			$room_id,
			$date_str
		);

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	private static function parse_breaks( $breaks_json, $date_str ) {
		$breaks = array();
		if ( empty( $breaks_json ) ) {
			return $breaks;
		}

		$decoded = json_decode( $breaks_json, true );
		if ( ! is_array( $decoded ) ) {
			return $breaks;
		}

		foreach ( $decoded as $break ) {
			if ( empty( $break['start'] ) || empty( $break['end'] ) ) {
				continue;
			}

			$break_start = self::build_timestamp( $date_str, $break['start'] );
			$break_end = self::build_timestamp( $date_str, $break['end'] );
			if ( ! $break_start || ! $break_end || $break_end <= $break_start ) {
				continue;
			}

			$breaks[] = array(
				'start' => $break_start,
				'end'   => $break_end,
			);
		}

		return $breaks;
	}

	private static function overlaps_any_interval( $slot_start, $slot_end, $intervals ) {
		foreach ( $intervals as $interval ) {
			if ( $slot_start < $interval['end'] && $slot_end > $interval['start'] ) {
				return true;
			}
		}

		return false;
	}

	private static function overlaps_any_booking( $slot_start, $slot_end, $bookings, $date_str ) {
		foreach ( $bookings as $booking ) {
			$booking_start = self::build_timestamp( $date_str, $booking['start_time'] );
			$booking_end = self::build_timestamp( $date_str, $booking['end_time'] );
			if ( ! $booking_start || ! $booking_end ) {
				continue;
			}

			if ( $slot_start < $booking_end && $slot_end > $booking_start ) {
				return true;
			}
		}

		return false;
	}

	private static function build_timestamp( $date_str, $time_str ) {
		$normalized_time = self::normalize_time_string( $time_str );
		if ( '' === $normalized_time ) {
			return false;
		}

		return strtotime( $date_str . ' ' . $normalized_time );
	}

	private static function normalize_time_string( $time_str ) {
		$time_str = trim( (string) $time_str );
		if ( preg_match( '/^\d{2}:\d{2}$/', $time_str ) ) {
			return $time_str . ':00';
		}

		if ( preg_match( '/^\d{2}:\d{2}:\d{2}$/', $time_str ) ) {
			return $time_str;
		}

		return '';
	}

	private static function normalize_day_type( $day_type ) {
		$day_type = strtolower( trim( (string) $day_type ) );
		$day_type = str_replace( array( 'ă', 'â', 'î', 'ș', 'ş', 'ț', 'ţ' ), array( 'a', 'a', 'i', 's', 's', 't', 't' ), $day_type );
		$day_type = preg_replace( '/[^a-z]/', '', $day_type );

		$aliases = array(
			'daily' => array( 'daily', 'zilnic', 'everyday', 'alldays', 'allweek' ),
			'weekdays' => array( 'weekdays', 'weekday', 'weekdaysonly', 'workdays', 'ww', 'luni-vineri', 'lunivineri', 'lvmf' ),
			'weekends' => array( 'weekends', 'weekend', 'we', 'saturdaysunday', 'sambataduminica' ),
			'monday' => array( 'monday', 'mon', 'luni' ),
			'tuesday' => array( 'tuesday', 'tue', 'tues', 'marti' ),
			'wednesday' => array( 'wednesday', 'wed', 'miercuri' ),
			'thursday' => array( 'thursday', 'thu', 'thur', 'thurs', 'joi' ),
			'friday' => array( 'friday', 'fri', 'vineri' ),
			'saturday' => array( 'saturday', 'sat', 'sambata' ),
			'sunday' => array( 'sunday', 'sun', 'duminica' ),
		);

		foreach ( $aliases as $normalized => $values ) {
			if ( in_array( $day_type, $values, true ) ) {
				return $normalized;
			}
		}

		return $day_type;
	}
}
