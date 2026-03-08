<?php

class CABA_Frontend_Ajax {

	public static function handle_request() {
		check_ajax_referer( 'cab_public_nonce', 'nonce' );

		$route = isset( $_POST['route'] ) ? sanitize_text_field( $_POST['route'] ) : '';

		switch ( $route ) {
			case 'get_services': self::get_services(); break;
			case 'get_slots': self::get_slots(); break;
			case 'submit_booking': self::submit_booking(); break;
			default: wp_send_json_error( 'Invalid route' );
		}
	}

	private static function get_services() {
		global $wpdb;
		$services = CABA_DB::get_services_with_relations();
		$categories = CABA_DB::get_results('categories');
		
		$grouped = array();
		foreach($categories as $cat) {
			$grouped[$cat['id']] = array(
				'id' => $cat['id'],
				'name' => $cat['name'],
				'description' => $cat['description'],
				'services' => array()
			);
		}
		
		// If a service has no cat, put it in 'Altele'
		$grouped[0] = array(
			'id' => 0,
			'name' => 'Altele',
			'description' => '',
			'services' => array()
		);
		
		foreach($services as $srv) {
			$cat_id = $srv['category_id'] ? $srv['category_id'] : 0;
            $srv['schedules'] = CABA_DB::get_by('schedules', 'service_id', $srv['id']);
			$grouped[$cat_id]['services'][] = $srv;
		}
		
		// Remove empty categories
		$final = array();
		foreach($grouped as $g) {
			if(count($g['services']) > 0) {
				$final[] = $g;
			}
		}

		wp_send_json_success( $final );
	}

	private static function get_slots() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cab-availability.php';
		$service_id = intval( $_POST['service_id'] );
		$date = sanitize_text_field( $_POST['date'] );
		
		if(!$service_id || !$date) {
			wp_send_json_error('Missing parameters');
		}

		$slots = CABA_Availability::get_available_slots( $service_id, $date );
		wp_send_json_success( $slots );
	}

	private static function submit_booking() {
		$service_id = intval( $_POST['service_id'] );
		$date = sanitize_text_field( $_POST['date'] );
		$start_time = sanitize_text_field( $_POST['start_time'] );
		$end_time = sanitize_text_field( $_POST['end_time'] );
		$payment_method = sanitize_text_field( $_POST['payment_method'] ); // 'online' or 'onsite'
		
		$first_name = sanitize_text_field( $_POST['first_name'] );
		$last_name = sanitize_text_field( $_POST['last_name'] );
		$email = sanitize_email( $_POST['email'] );
		$phone = sanitize_text_field( $_POST['phone'] );

		$participants_count = isset($_POST['participants_count']) ? intval($_POST['participants_count']) : 1;
		$pricing_option_index = isset($_POST['pricing_option_index']) ? intval($_POST['pricing_option_index']) : -1;

		if(empty($first_name) || empty($last_name) || empty($email) || empty($phone)) {
			wp_send_json_error('Vă rugăm completați toate datele de contact.');
		}

		// Double check availability to prevent race conditions
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cab-availability.php';
		$slots = CABA_Availability::get_available_slots( $service_id, $date );
		
		$is_valid_slot = false;
		foreach($slots as $s) {
			if($s['start_time'] == substr($start_time, 0, 5)) {
				$is_valid_slot = true;
				break;
			}
		}
		
		if(!$is_valid_slot) {
			wp_send_json_error('Ne pare rău, acest interval orar nu mai este disponibil.');
		}

		$service = CABA_DB::get_row('services', $service_id);
		
		// Pricing Calculation
		$final_price = $service['price'];
		$options = json_decode($service['pricing_options'], true);
		if ($pricing_option_index >= 0 && !empty($options[$pricing_option_index])) {
			$final_price = $options[$pricing_option_index]['price'];
			// Update duration/end_time if the option has a specific duration
			if (!empty($options[$pricing_option_index]['duration'])) {
				$duration = $options[$pricing_option_index]['duration'];
				$end_time = date('H:i:s', strtotime($start_time) + ($duration * 60));
			}
		}

		if ($service['is_per_person']) {
			$final_price = $final_price * $participants_count;
		}

		// Handle Customer
		// Check if customer exists by email
		global $wpdb;
		$table_cust = CABA_DB::get_table_name('customers');
		$cust_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_cust WHERE email = %s", $email));
		
		if(!$cust_id) {
			$cust_id = CABA_DB::insert('customers', array(
				'first_name' => $first_name,
				'last_name' => $last_name,
				'email' => $email,
				'phone' => $phone
			));
		} else {
			CABA_DB::update('customers', array(
				'first_name' => $first_name,
				'last_name' => $last_name,
				'phone' => $phone
			), array('id' => $cust_id));
		}

		$status = ($payment_method == 'onsite') ? 'pending_payment_onsite' : 'pending';

		// Create Booking
		$booking_id = CABA_DB::insert('bookings', array(
			'service_id' => $service_id,
			'customer_id' => $cust_id,
			'booking_date' => $date,
			'start_time' => $start_time,
			'end_time' => $end_time,
			'participants_count' => $participants_count,
			'status' => $status,
			'payment_method' => $payment_method,
			'total_amount' => $final_price
		));

		// Email Notifications (Using WP Mail)
		self::send_notifications($booking_id, $service, $first_name . ' ' . $last_name, $email, $phone, $date, $start_time, $status);

		// WooCommerce Integration Trigger
		if($payment_method == 'online' && get_option('cab_wc_enabled', 0)) {
			$wc_url = self::process_woocommerce($booking_id, $service, $final_price);
			wp_send_json_success(array('redirect' => $wc_url));
		}

		wp_send_json_success(array('message' => 'Rezervare creată cu succes! Veți primi un email de confirmare în scurt timp.'));
	}

	private static function process_woocommerce($booking_id, $service, $final_price) {
		$product_id = intval(get_option('cab_wc_product_id', 0));
		
		if(class_exists('WooCommerce') && $product_id > 0) {
			WC()->cart->empty_cart();
			
			// Add product to cart with custom price
			$cart_item_data = array(
				'cab_booking_id' => $booking_id,
				'cab_custom_price' => $final_price,
				'cab_service_name' => 'Rezervare: ' . $service['name']
			);
			WC()->cart->add_to_cart($product_id, 1, 0, array(), $cart_item_data);
			
			return wc_get_checkout_url();
		}

		return site_url(); // Fallback if WC is missing
	}

	private static function send_notifications($booking_id, $service, $customer_name, $customer_email, $phone, $date, $start_time, $status_plata) {
		$admin_email = get_option('cab_admin_email_address', get_option('admin_email'));
		
		$vars = array(
			'{nume_client}' => $customer_name,
			'{nume_serviciu}' => $service['name'],
			'{data_rezervare}' => date('d.m.Y', strtotime($date)),
			'{ora_rezervare}' => substr($start_time, 0, 5),
			'{telefon}' => $phone,
			'{status_plata}' => ($status_plata == 'pending_payment_onsite' ? 'Plata se va face la locație' : 'Așteaptă plata online (WooCommerce)')
		);

		// Admin
		$admin_tpl = get_option('cab_email_admin');
		if($admin_tpl) {
			$adminBody = str_replace(array_keys($vars), array_values($vars), $admin_tpl);
			wp_mail($admin_email, 'Rezervare Nouă - ' . $customer_name, nl2br($adminBody), array('Content-Type: text/html; charset=UTF-8'));
		}

		// Customer
		$customer_tpl = get_option('cab_email_confirm');
		if($customer_tpl && $status_plata == 'pending_payment_onsite') {
			$customerBody = str_replace(array_keys($vars), array_values($vars), $customer_tpl);
			wp_mail($customer_email, 'Confirmare Rezervare Colosseum', nl2br($customerBody), array('Content-Type: text/html; charset=UTF-8'));
		}
	}
}
