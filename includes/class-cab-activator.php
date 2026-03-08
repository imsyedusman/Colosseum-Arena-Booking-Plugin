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
			min_participants int(11) DEFAULT 1,
			max_participants int(11) DEFAULT 20,
			is_per_person tinyint(1) DEFAULT 0,
			pricing_options text, /* JSON string of array with {name, duration, price} */
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
			participants_count int(11) DEFAULT 1,
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

		$inserted = array('categories' => 0, 'rooms' => 0, 'employees' => 0, 'services' => 0);

		// 1. Seed Categories
		$table_cat = $wpdb->prefix . 'cab_categories';
		$categories = array('Petreceri copii', 'Activități', 'Teambuilding', 'Evenimente școlare');
		$cat_ids = array();
		foreach ($categories as $cat) {
			$id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_cat WHERE name = %s", $cat ) );
			if (!$id) {
				$wpdb->insert( $table_cat, array( 'name' => $cat ) );
				$id = $wpdb->insert_id;
				$inserted['categories']++;
			}
			$cat_ids[$cat] = $id;
		}

		// 2. Seed Rooms
		$table_rooms = $wpdb->prefix . 'cab_rooms';
		$rooms = array('Birthday Room', 'VIP Birthday Room');
		$room_ids = array();
		foreach ($rooms as $room) {
			$id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_rooms WHERE name = %s", $room ) );
			if (!$id) {
				$wpdb->insert( $table_rooms, array( 'name' => $room, 'capacity' => 1 ) );
				$id = $wpdb->insert_id;
				$inserted['rooms']++;
			}
			$room_ids[$room] = $id;
		}

		// 3. Seed Employees
		$table_employees = $wpdb->prefix . 'cab_employees';
		$employees = array('Party Bookings', 'VIP Bookings', 'Colosseum Staff');
		$emp_ids = array();
		foreach ($employees as $emp) {
			$id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_employees WHERE name = %s", $emp ) );
			if (!$id) {
				$wpdb->insert( $table_employees, array( 'name' => $emp ) );
				$id = $wpdb->insert_id;
				$inserted['employees']++;
			}
			$emp_ids[$emp] = $id;
		}

		// 4. Seed Services
		$table_services = $wpdb->prefix . 'cab_services';
		$table_schedules = $wpdb->prefix . 'cab_schedules';

		// Birthday schedules array
		$birthday_schedules = array(
			array('day_type' => 'weekdays', 'start_time' => '17:00:00', 'end_time' => '20:00:00'),
			array('day_type' => 'weekdays', 'start_time' => '18:00:00', 'end_time' => '21:00:00'),
			array('day_type' => 'weekends', 'start_time' => '11:00:00', 'end_time' => '14:00:00'),
			array('day_type' => 'weekends', 'start_time' => '13:00:00', 'end_time' => '16:00:00'),
			array('day_type' => 'weekends', 'start_time' => '16:00:00', 'end_time' => '19:00:00'),
			array('day_type' => 'weekends', 'start_time' => '18:00:00', 'end_time' => '21:00:00')
		);

		// Birthday Services array
		$bday_services = array(
			array('name' => 'Petrecere copii Standard', 'room' => 'Birthday Room', 'employee' => 'Party Bookings'),
			array('name' => 'Petrecere copii Premium', 'room' => 'Birthday Room', 'employee' => 'Party Bookings'),
			array('name' => 'Petrecere copii VIP', 'room' => 'VIP Birthday Room', 'employee' => 'VIP Bookings'),
		);

		foreach ($bday_services as $bs) {
			$srv_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_services WHERE name = %s", $bs['name'] ) );
			if (!$srv_id) {
				$wpdb->insert($table_services, array(
					'name' => $bs['name'],
					'category_id' => $cat_ids['Petreceri copii'],
					'room_id' => $room_ids[$bs['room']],
					'employee_id' => $emp_ids[$bs['employee']],
					'duration' => 180,
					'price' => 1000.00,
					'min_participants' => 1,
					'max_participants' => 20
				));
				$srv_id = $wpdb->insert_id;
				$inserted['services']++;

				foreach ($birthday_schedules as $sch) {
					$wpdb->insert($table_schedules, array(
						'service_id' => $srv_id,
						'day_type' => $sch['day_type'],
						'start_time' => $sch['start_time'],
						'end_time' => $sch['end_time']
					));
				}
			}
		}

		// Activity Services array
		$activity_config = array(
            array(
                'name' => 'LaserTag', 
                'duration' => 20, 
                'price' => 30.00, 
                'min_p' => 6, 
                'max_p' => 20, 
                'per_person' => 1,
                'options' => wp_json_encode(array(
                    array('name' => '1 Joc (20 min)', 'duration' => 20, 'price' => 30.00),
                    array('name' => '2 Jocuri (40 min)', 'duration' => 40, 'price' => 50.00)
                ))
            ),
            array(
                'name' => 'Human Foosball', 
                'duration' => 15, 
                'price' => 25.00, 
                'min_p' => 8, 
                'max_p' => 20, 
                'per_person' => 1,
                'options' => wp_json_encode(array(
                    array('name' => '1 Joc (15 min)', 'duration' => 15, 'price' => 25.00),
                    array('name' => '2 Jocuri (30 min)', 'duration' => 30, 'price' => 40.00)
                ))
            ),
            array(
                'name' => 'Paintball', 
                'duration' => 60, 
                'price' => 60.00, 
                'min_p' => 6, 
                'max_p' => 10, 
                'per_person' => 1,
                'options' => wp_json_encode(array(
                    array('name' => 'Pachet 200 bile (60 min)', 'duration' => 60, 'price' => 60.00),
                    array('name' => 'Pachet 400 bile (90 min)', 'duration' => 90, 'price' => 100.00)
                ))
            ),
            array(
                'name' => 'Gotcha', 
                'duration' => 30, 
                'price' => 35.00, 
                'min_p' => 6, 
                'max_p' => 12, 
                'per_person' => 1,
                'options' => wp_json_encode(array(
                    array('name' => '1 Joc (30 min)', 'duration' => 30, 'price' => 35.00),
                    array('name' => '2 Jocuri (60 min)', 'duration' => 60, 'price' => 60.00)
                ))
            ),
            array(
                'name' => 'GellyBall', 
                'duration' => 30, 
                'price' => 35.00, 
                'min_p' => 6, 
                'max_p' => 10, 
                'per_person' => 1,
                'options' => wp_json_encode(array(
                    array('name' => '1 Joc (30 min)', 'duration' => 30, 'price' => 35.00),
                    array('name' => '2 Jocuri (60 min)', 'duration' => 60, 'price' => 60.00)
                ))
            ),
            array(
                'name' => 'Jocuri Interactive', 
                'duration' => 30, 
                'price' => 30.00, 
                'min_p' => 6, 
                'max_p' => 16, 
                'per_person' => 1,
                'options' => wp_json_encode(array(
                    array('name' => '1 Joc (30 min)', 'duration' => 30, 'price' => 30.00),
                    array('name' => '2 Jocuri (60 min)', 'duration' => 60, 'price' => 50.00)
                ))
            )
        );

		foreach ($activity_config as $act) {
			$srv_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_services WHERE name = %s", $act['name'] ) );
			if (!$srv_id) {
				$wpdb->insert($table_services, array(
					'name' => $act['name'],
					'category_id' => $cat_ids['Activități'],
                    'room_id' => 0, // No specific room conflict
					'employee_id' => $emp_ids['Colosseum Staff'],
					'duration' => $act['duration'],
					'price' => $act['price'],
					'min_participants' => $act['min_p'],
					'max_participants' => $act['max_p'],
					'is_per_person' => $act['per_person'],
                    'pricing_options' => $act['options']
				));
                $srv_id = $wpdb->insert_id;
				$inserted['services']++;

                // Assign Schedule
                if ($act['name'] === 'GellyBall') {
                    $wpdb->insert($table_schedules, array(
                        'service_id' => $srv_id,
                        'day_type' => 'wednesday',
                        'start_time' => '12:00:00',
                        'end_time' => '20:00:00'
                    ));
                    $wpdb->insert($table_schedules, array(
                        'service_id' => $srv_id,
                        'day_type' => 'thursday',
                        'start_time' => '12:00:00',
                        'end_time' => '20:00:00'
                    ));
                    $wpdb->insert($table_schedules, array(
                        'service_id' => $srv_id,
                        'day_type' => 'friday',
                        'start_time' => '12:00:00',
                        'end_time' => '20:00:00'
                    ));
                    $wpdb->insert($table_schedules, array(
                        'service_id' => $srv_id,
                        'day_type' => 'weekends', // Saturday and Sunday
                        'start_time' => '12:00:00',
                        'end_time' => '20:00:00'
                    ));
                } else {
                    $wpdb->insert($table_schedules, array(
                        'service_id' => $srv_id,
                        'day_type' => 'daily',
                        'start_time' => '10:00:00',
                        'end_time' => '22:00:00'
                    ));
                }
			} else {
                // If service already exists, ensure it has at least one schedule
                $has_sch = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_schedules WHERE service_id = %d", $srv_id));
                if (!$has_sch) {
                    $wpdb->insert($table_schedules, array(
                        'service_id' => $srv_id,
                        'day_type' => 'daily',
                        'start_time' => '10:00:00',
                        'end_time' => '22:00:00'
                    ));
                }
            }
		}
		
		return $inserted;
	}

}
