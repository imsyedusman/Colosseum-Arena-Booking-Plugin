<?php

class CABA_Ajax {

	public static function handle_admin_ajax() {
		check_ajax_referer( 'cab_admin_nonce', 'nonce' );

		$route = isset( $_POST['route'] ) ? sanitize_text_field( $_POST['route'] ) : '';

		switch ( $route ) {
			case 'save_category': self::save_category(); break;
			case 'delete_category': self::delete_category(); break;
			case 'save_room': self::save_room(); break;
			case 'delete_room': self::delete_room(); break;
			case 'save_employee': self::save_employee(); break;
			case 'delete_employee': self::delete_employee(); break;
			case 'save_customer': self::save_customer(); break;
			case 'delete_customer': self::delete_customer(); break;

			// Services
			case 'save_service': self::save_service(); break;
			case 'delete_service': self::delete_service(); break;
			case 'save_schedules': self::save_schedules(); break;
			case 'get_schedules': self::get_schedules(); break;

			// Bookings & Settings
			case 'save_booking': self::save_booking(); break;
			case 'delete_booking': self::delete_booking(); break;
			case 'cancel_booking': self::cancel_booking(); break;
			case 'get_calendar_events': self::get_calendar_events(); break;
			case 'update_booking_dates': self::update_booking_dates(); break;
			case 'import_seed_data': self::import_seed_data(); break;
			case 'save_settings': self::save_settings(); break;

			default:
				wp_send_json_error( 'Invalid route' );
		}
	}

	// --- Categories ---
	private static function save_category() {
		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$name = sanitize_text_field( $_POST['name'] );
		$desc = sanitize_textarea_field( $_POST['description'] );
		if ( empty( $name ) ) wp_send_json_error( 'Name is required' );
		if ( $id > 0 ) {
			CABA_DB::update( 'categories', array( 'name' => $name, 'description' => $desc ), array( 'id' => $id ) );
			wp_send_json_success( 'Categorie actualizată' );
		} else {
			CABA_DB::insert( 'categories', array( 'name' => $name, 'description' => $desc ) );
			wp_send_json_success( 'Categorie adăugată' );
		}
	}

	private static function delete_category() {
		$id = intval( $_POST['id'] );
		CABA_DB::delete( 'categories', array( 'id' => $id ) );
		wp_send_json_success( 'Categorie ștearsă' );
	}

	// --- Rooms ---
	private static function save_room() {
		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$name = sanitize_text_field( $_POST['name'] );
		$capacity = isset( $_POST['capacity'] ) ? intval( $_POST['capacity'] ) : 1;
		if ( empty( $name ) ) wp_send_json_error( 'Name is required' );
		if ( $id > 0 ) {
			CABA_DB::update( 'rooms', array( 'name' => $name, 'capacity' => $capacity ), array( 'id' => $id ) );
			wp_send_json_success( 'Cameră actualizată' );
		} else {
			CABA_DB::insert( 'rooms', array( 'name' => $name, 'capacity' => $capacity ) );
			wp_send_json_success( 'Cameră adăugată' );
		}
	}

	private static function delete_room() {
		$id = intval( $_POST['id'] );
		CABA_DB::delete( 'rooms', array( 'id' => $id ) );
		wp_send_json_success( 'Cameră ștearsă' );
	}

	// --- Employees ---
	private static function save_employee() {
		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$name = sanitize_text_field( $_POST['name'] );
		$email = sanitize_email( $_POST['email'] );
		$phone = sanitize_text_field( $_POST['phone'] );
		if ( empty( $name ) ) wp_send_json_error( 'Name is required' );
		if ( $id > 0 ) {
			CABA_DB::update( 'employees', array( 'name' => $name, 'email' => $email, 'phone' => $phone ), array( 'id' => $id ) );
			wp_send_json_success( 'Angajat actualizat' );
		} else {
			CABA_DB::insert( 'employees', array( 'name' => $name, 'email' => $email, 'phone' => $phone ) );
			wp_send_json_success( 'Angajat adăugat' );
		}
	}

	private static function delete_employee() {
		$id = intval( $_POST['id'] );
		CABA_DB::delete( 'employees', array( 'id' => $id ) );
		wp_send_json_success( 'Angajat șters' );
	}

	// --- Customers ---
	private static function save_customer() {
		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$first_name = sanitize_text_field( $_POST['first_name'] );
		$last_name = sanitize_text_field( $_POST['last_name'] );
		$email = sanitize_email( $_POST['email'] );
		$phone = sanitize_text_field( $_POST['phone'] );
		if ( empty( $first_name ) || empty( $last_name ) ) wp_send_json_error( 'Numele complet este obligatoriu' );
		if ( $id > 0 ) {
			CABA_DB::update( 'customers', array( 'first_name' => $first_name, 'last_name' => $last_name, 'email' => $email, 'phone' => $phone ), array( 'id' => $id ) );
			wp_send_json_success( 'Client actualizat' );
		} else {
			CABA_DB::insert( 'customers', array( 'first_name' => $first_name, 'last_name' => $last_name, 'email' => $email, 'phone' => $phone ) );
			wp_send_json_success( 'Client adăugat' );
		}
	}

	private static function delete_customer() {
		$id = intval( $_POST['id'] );
		CABA_DB::delete( 'customers', array( 'id' => $id ) );
		wp_send_json_success( 'Client șters' );
	}

	// --- Services & Schedules ---
	private static function save_service() {
		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$name = sanitize_text_field( $_POST['name'] );
		$category_id = intval( $_POST['category_id'] );
		$duration = intval( $_POST['duration'] );
		$price = floatval( $_POST['price'] );
		$room_id = intval( $_POST['room_id'] );
		$employee_id = intval( $_POST['employee_id'] );

        $min_participants = isset($_POST['min_participants']) ? intval($_POST['min_participants']) : 1;
        $max_participants = isset($_POST['max_participants']) ? intval($_POST['max_participants']) : 20;
        $is_per_person = isset($_POST['is_per_person']) ? intval($_POST['is_per_person']) : 0;
        $pricing_options = isset($_POST['pricing_options']) ? wp_unslash($_POST['pricing_options']) : '';

		if ( empty( $name ) ) wp_send_json_error( 'Numele serviciului este obligatoriu' );

		$data = array(
			'name' => $name,
			'category_id' => $category_id,
			'duration' => $duration,
			'price' => $price,
			'room_id' => $room_id,
			'employee_id' => $employee_id,
            'min_participants' => $min_participants,
            'max_participants' => $max_participants,
            'is_per_person' => $is_per_person,
            'pricing_options' => $pricing_options
		);

		if ( $id > 0 ) {
			CABA_DB::update( 'services', $data, array( 'id' => $id ) );
            $service_id = $id;
		} else {
			CABA_DB::insert( 'services', $data );
            global $wpdb;
            $service_id = $wpdb->insert_id;
		}

        // Save inline schedules
        $schedules = isset($_POST['schedules']) ? $_POST['schedules'] : array();
        CABA_DB::delete('schedules', array('service_id' => $service_id));
        foreach($schedules as $sch) {
            if (!empty($sch['day_type']) && !empty($sch['start_time']) && !empty($sch['end_time'])) {
                CABA_DB::insert('schedules', array(
                    'service_id' => $service_id,
                    'day_type' => sanitize_text_field($sch['day_type']),
                    'start_time' => sanitize_text_field($sch['start_time']),
                    'end_time' => sanitize_text_field($sch['end_time'])
                ));
            }
        }

        wp_send_json_success( $id > 0 ? 'Serviciu actualizat' : 'Serviciu adăugat' );
	}

	private static function delete_service() {
		$id = intval( $_POST['id'] );
		CABA_DB::delete( 'services', array( 'id' => $id ) );
		// Also delete related schedules
		CABA_DB::delete( 'schedules', array( 'service_id' => $id ) );
		wp_send_json_success( 'Serviciu șters' );
	}

	private static function get_schedules() {
		$service_id = intval( $_POST['service_id'] );
		$schedules = CABA_DB::get_by('schedules', 'service_id', $service_id);
		wp_send_json_success( $schedules );
	}

	private static function save_schedules() {
		$service_id = intval( $_POST['service_id'] );
		$schedules = isset($_POST['schedules']) ? $_POST['schedules'] : array();
		
		// Clear existing schedules for this service
		CABA_DB::delete('schedules', array('service_id' => $service_id));
		
		foreach($schedules as $sch) {
			if (!empty($sch['day_type']) && !empty($sch['start_time']) && !empty($sch['end_time'])) {
				CABA_DB::insert('schedules', array(
					'service_id' => $service_id,
					'day_type' => sanitize_text_field($sch['day_type']),
					'start_time' => sanitize_text_field($sch['start_time']),
					'end_time' => sanitize_text_field($sch['end_time'])
				));
			}
		}
		wp_send_json_success('Orare actualizate cu succes.');
	}

	// --- Settings ---
	private static function import_seed_data() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cab-activator.php';
		$counts = Colosseum_Arena_Booking_Activator::seed_data();
		
		$msg = 'Datele demo au fost adăugate cu succes. ';
		$msg .= 'Categorii: ' . $counts['categories'] . ', ';
		$msg .= 'Servicii: ' . $counts['services'] . ', ';
		$msg .= 'Camere: ' . $counts['rooms'] . '.';

		wp_send_json_success($msg);
	}

    private static function save_settings() {
        $wc_enabled = isset($_POST['wc_enabled']) ? 1 : 0;
        $wc_product_id = intval($_POST['wc_product_id']);
        
        update_option('cab_wc_enabled', $wc_enabled);
        update_option('cab_wc_product_id', $wc_product_id);
        
        $email_confirm = wp_kses_post($_POST['email_confirm']);
        $email_admin = wp_kses_post($_POST['email_admin']);
        $email_cancel = wp_kses_post($_POST['email_cancel']);
        $admin_email_address = sanitize_email($_POST['admin_email_address']);
        
        update_option('cab_email_confirm', $email_confirm);
        update_option('cab_email_admin', $email_admin);
        update_option('cab_email_cancel', $email_cancel);
        update_option('cab_admin_email_address', $admin_email_address);
        
        wp_send_json_success('Setările și Notificările au fost salvate.');
    }
    
    // --- Bookings ---
    private static function save_booking() {
		$id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : 0;
		$service_id = intval( $_POST['service_id'] );
		$customer_id = intval( $_POST['customer_id'] );
		$booking_date = sanitize_text_field( $_POST['booking_date'] );
		$start_time = sanitize_text_field( $_POST['start_time'] );
		$end_time = sanitize_text_field( $_POST['end_time'] );
		$status = sanitize_text_field( $_POST['status'] );
		$payment_method = sanitize_text_field( $_POST['payment_method'] );
        $total_amount = floatval($_POST['total_amount']);
        $participants_count = isset($_POST['participants_count']) ? intval($_POST['participants_count']) : 1;

		$data = array(
			'service_id' => $service_id,
			'customer_id' => $customer_id,
			'booking_date' => $booking_date,
			'start_time' => $start_time,
			'end_time' => $end_time,
			'status' => $status,
			'payment_method' => $payment_method,
            'total_amount' => $total_amount,
            'participants_count' => $participants_count
		);

		if ( $id > 0 ) {
			CABA_DB::update( 'bookings', $data, array( 'id' => $id ) );
			wp_send_json_success( 'Rezervare actualizată' );
		} else {
			CABA_DB::insert( 'bookings', $data );
			wp_send_json_success( 'Rezervare adăugată' );
		}
	}

	private static function cancel_booking() {
		$id = intval( $_POST['id'] );
		CABA_DB::update( 'bookings', array('status' => 'cancelled'), array( 'id' => $id ) );
		wp_send_json_success( 'Rezervare anulată' );
	}

	private static function delete_booking() {
		$id = intval( $_POST['id'] );
		CABA_DB::delete( 'bookings', array( 'id' => $id ) );
		wp_send_json_success( 'Rezervare ștearsă' );
	}

	private static function get_calendar_events() {
		global $wpdb;
		$bookings = CABA_DB::get_bookings_with_relations();
		$events = array();

		foreach($bookings as $b) {
			$title = $b['first_name'] . ' ' . $b['last_name'];
			if($b['service_name']) {
				$title .= ' - ' . $b['service_name'];
			}

			// Color logic based on status
			$color = '#3b82f6'; // blue (pending)
			if($b['status'] == 'confirmed') $color = '#10b981'; // green
			if($b['status'] == 'cancelled') $color = '#ef4444'; // red
			if($b['status'] == 'pending_payment_onsite') $color = '#f59e0b'; // orange

			$events[] = array(
				'id' => $b['id'],
				'title' => $title,
				'start' => $b['booking_date'] . 'T' . $b['start_time'],
				'end' => $b['booking_date'] . 'T' . $b['end_time'],
				'backgroundColor' => $color,
				'borderColor' => $color,
				'extendedProps' => array(
					'status' => $b['status']
				)
			);
		}
		
		wp_send_json($events);
	}

	private static function update_booking_dates() {
		$id = intval( $_POST['id'] );
		$booking_date = sanitize_text_field( $_POST['booking_date'] );
		$start_time = sanitize_text_field( $_POST['start_time'] );
		$end_time = sanitize_text_field( $_POST['end_time'] );

		CABA_DB::update( 'bookings', array(
			'booking_date' => $booking_date,
			'start_time' => $start_time,
			'end_time' => $end_time
		), array( 'id' => $id ) );
		
		wp_send_json_success( 'Data/ora rezervării au fost actualizate' );
	}
}
