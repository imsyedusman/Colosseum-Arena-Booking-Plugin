<?php

class Colosseum_Arena_Booking_Activator {

	public static function activate() {
		self::create_tables();
		self::seed_data();
	}

	public static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		// Tables
		$table_categories = $wpdb->prefix . 'cab_categories';
		$table_rooms      = $wpdb->prefix . 'cab_rooms';
		$table_employees  = $wpdb->prefix . 'cab_employees';
		$table_services   = $wpdb->prefix . 'cab_services';
		$table_schedules  = $wpdb->prefix . 'cab_schedules';
		$table_customers  = $wpdb->prefix . 'cab_customers';
		$table_bookings   = $wpdb->prefix . 'cab_bookings';

		$sql = "
		CREATE TABLE $table_categories (
			id int(11) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			description text,
			PRIMARY KEY  (id)
		) $charset_collate;

		CREATE TABLE $table_rooms (
			id int(11) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			capacity int(11) DEFAULT 1,
			PRIMARY KEY  (id)
		) $charset_collate;

		CREATE TABLE $table_employees (
			id int(11) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			email varchar(255),
			phone varchar(255),
			PRIMARY KEY  (id)
		) $charset_collate;

		CREATE TABLE $table_services (
			id int(11) NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			category_id int(11),
			duration int(11) NOT NULL DEFAULT 60,
			price decimal(10,2) NOT NULL DEFAULT 0.00,
			room_id int(11),
			employee_id int(11),
			PRIMARY KEY  (id)
		) $charset_collate;

		CREATE TABLE $table_schedules (
			id int(11) NOT NULL AUTO_INCREMENT,
			service_id int(11) NOT NULL,
			day_type varchar(50) NOT NULL, /* 'weekdays' or 'weekends' */
			start_time time NOT NULL,
			end_time time NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;

		CREATE TABLE $table_customers (
			id int(11) NOT NULL AUTO_INCREMENT,
			first_name varchar(255) NOT NULL,
			last_name varchar(255) NOT NULL,
			email varchar(255) NOT NULL,
			phone varchar(255) NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;

		CREATE TABLE $table_bookings (
			id int(11) NOT NULL AUTO_INCREMENT,
			service_id int(11) NOT NULL,
			customer_id int(11) NOT NULL,
			booking_date date NOT NULL,
			start_time time NOT NULL,
			end_time time NOT NULL,
			status varchar(50) NOT NULL DEFAULT 'pending', /* pending, confirmed, cancelled, ... */
			payment_method varchar(50), /* online, onsite */
			total_amount decimal(10,2) NOT NULL DEFAULT 0.00,
			wc_order_id int(11),
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id)
		) $charset_collate;
		";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	public static function seed_data() {
		global $wpdb;

		// 1. Seed Categories
		$table_cat = $wpdb->prefix . 'cab_categories';
		$cat_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_cat WHERE name = %s", 'Petreceri copii' ) );
		if ( ! $cat_id ) {
			$wpdb->insert( $table_cat, array( 'name' => 'Petreceri copii', 'description' => 'Categorii pentru petrecerile copiilor' ) );
			$cat_id = $wpdb->insert_id;
		}

		// 2. Seed Rooms
		$table_rooms = $wpdb->prefix . 'cab_rooms';
		$room_birthday_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_rooms WHERE name = %s", 'Birthday Room' ) );
		if ( ! $room_birthday_id ) {
			$wpdb->insert( $table_rooms, array( 'name' => 'Birthday Room', 'capacity' => 1 ) );
			$room_birthday_id = $wpdb->insert_id;
		}
		
		$room_vip_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_rooms WHERE name = %s", 'VIP Birthday Room' ) );
		if ( ! $room_vip_id ) {
			$wpdb->insert( $table_rooms, array( 'name' => 'VIP Birthday Room', 'capacity' => 1 ) );
			$room_vip_id = $wpdb->insert_id;
		}

		// 3. Seed Employees
		$table_employees = $wpdb->prefix . 'cab_employees';
		$emp_party_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_employees WHERE name = %s", 'Party Bookings' ) );
		if ( ! $emp_party_id ) {
			$wpdb->insert( $table_employees, array( 'name' => 'Party Bookings' ) );
			$emp_party_id = $wpdb->insert_id;
		}

		$emp_vip_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_employees WHERE name = %s", 'VIP Bookings' ) );
		if ( ! $emp_vip_id ) {
			$wpdb->insert( $table_employees, array( 'name' => 'VIP Bookings' ) );
			$emp_vip_id = $wpdb->insert_id;
		}

		// 4. Seed Services
		$table_services = $wpdb->prefix . 'cab_services';
		$table_schedules = $wpdb->prefix . 'cab_schedules';

		// STANDARD
		$srv_std_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_services WHERE name = %s", 'Petrecere copii Standard' ) );
		if ( ! $srv_std_id ) {
			$wpdb->insert( $table_services, array(
				'name' => 'Petrecere copii Standard',
				'category_id' => $cat_id,
				'duration' => 150, // 2.5 hours
				'price' => 1000.00,
				'room_id' => $room_birthday_id,
				'employee_id' => $emp_party_id
			) );
			$srv_std_id = $wpdb->insert_id;
			
			// Schedule Standard
			$wpdb->insert( $table_schedules, array( 'service_id' => $srv_std_id, 'day_type' => 'weekdays', 'start_time' => '16:00:00', 'end_time' => '18:30:00' ) );
			$wpdb->insert( $table_schedules, array( 'service_id' => $srv_std_id, 'day_type' => 'weekdays', 'start_time' => '19:30:00', 'end_time' => '22:00:00' ) );
			$wpdb->insert( $table_schedules, array( 'service_id' => $srv_std_id, 'day_type' => 'weekends', 'start_time' => '12:00:00', 'end_time' => '14:30:00' ) );
			$wpdb->insert( $table_schedules, array( 'service_id' => $srv_std_id, 'day_type' => 'weekends', 'start_time' => '17:00:00', 'end_time' => '19:30:00' ) );
		}

		// PREMIUM
		$srv_prm_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_services WHERE name = %s", 'Petrecere copii Premium' ) );
		if ( ! $srv_prm_id ) {
			$wpdb->insert( $table_services, array(
				'name' => 'Petrecere copii Premium',
				'category_id' => $cat_id,
				'duration' => 180, // 3 hours
				'price' => 1500.00,
				'room_id' => $room_birthday_id,
				'employee_id' => $emp_party_id
			) );
			$srv_prm_id = $wpdb->insert_id;

			// Schedule Premium
			$wpdb->insert( $table_schedules, array( 'service_id' => $srv_prm_id, 'day_type' => 'weekdays', 'start_time' => '17:00:00', 'end_time' => '20:00:00' ) );
			$wpdb->insert( $table_schedules, array( 'service_id' => $srv_prm_id, 'day_type' => 'weekdays', 'start_time' => '18:00:00', 'end_time' => '21:00:00' ) );
			$wpdb->insert( $table_schedules, array( 'service_id' => $srv_prm_id, 'day_type' => 'weekends', 'start_time' => '11:00:00', 'end_time' => '14:00:00' ) );
			$wpdb->insert( $table_schedules, array( 'service_id' => $srv_prm_id, 'day_type' => 'weekends', 'start_time' => '13:00:00', 'end_time' => '16:00:00' ) );
			$wpdb->insert( $table_schedules, array( 'service_id' => $srv_prm_id, 'day_type' => 'weekends', 'start_time' => '16:00:00', 'end_time' => '19:00:00' ) );
			$wpdb->insert( $table_schedules, array( 'service_id' => $srv_prm_id, 'day_type' => 'weekends', 'start_time' => '18:00:00', 'end_time' => '21:00:00' ) );
		}

		// VIP
		$srv_vip_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_services WHERE name = %s", 'Petrecere copii VIP' ) );
		if ( ! $srv_vip_id ) {
			$wpdb->insert( $table_services, array(
				'name' => 'Petrecere copii VIP',
				'category_id' => $cat_id,
				'duration' => 180, // 3 hours
				'price' => 2000.00,
				'room_id' => $room_vip_id,
				'employee_id' => $emp_vip_id
			) );
			$srv_vip_id = $wpdb->insert_id;

			// Schedule VIP
			$wpdb->insert( $table_schedules, array( 'service_id' => $srv_vip_id, 'day_type' => 'weekdays', 'start_time' => '17:00:00', 'end_time' => '20:00:00' ) );
			$wpdb->insert( $table_schedules, array( 'service_id' => $srv_vip_id, 'day_type' => 'weekends', 'start_time' => '11:00:00', 'end_time' => '14:00:00' ) );
			$wpdb->insert( $table_schedules, array( 'service_id' => $srv_vip_id, 'day_type' => 'weekends', 'start_time' => '15:00:00', 'end_time' => '18:00:00' ) );
			$wpdb->insert( $table_schedules, array( 'service_id' => $srv_vip_id, 'day_type' => 'weekends', 'start_time' => '19:00:00', 'end_time' => '22:00:00' ) );
		}
	}

}
