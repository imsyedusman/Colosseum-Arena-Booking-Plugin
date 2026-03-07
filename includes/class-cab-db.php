<?php

class CABA_DB {

	public static function get_table_name( $table ) {
		global $wpdb;
		return $wpdb->prefix . 'cab_' . $table;
	}

	public static function get_results( $table, $orderBy = 'id', $order = 'ASC' ) {
		global $wpdb;
		$table_name = self::get_table_name( $table );
		$sql = "SELECT * FROM $table_name ORDER BY $orderBy $order";
		return $wpdb->get_results( $sql, ARRAY_A );
	}

	public static function get_row( $table, $id ) {
		global $wpdb;
		$table_name = self::get_table_name( $table );
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id ), ARRAY_A );
	}

	public static function get_by( $table, $column, $value ) {
		global $wpdb;
		$table_name = self::get_table_name( $table );
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE $column = %s", $value ), ARRAY_A );
	}

	public static function insert( $table, $data ) {
		global $wpdb;
		$table_name = self::get_table_name( $table );
		$wpdb->insert( $table_name, $data );
		return $wpdb->insert_id;
	}

	public static function update( $table, $data, $where ) {
		global $wpdb;
		$table_name = self::get_table_name( $table );
		return $wpdb->update( $table_name, $data, $where );
	}

	public static function delete( $table, $where ) {
		global $wpdb;
		$table_name = self::get_table_name( $table );
		return $wpdb->delete( $table_name, $where );
	}

	public static function get_services_with_relations() {
		global $wpdb;
		$table_serv = self::get_table_name('services');
		$table_cat = self::get_table_name('categories');
		$table_rooms = self::get_table_name('rooms');
		$table_emp = self::get_table_name('employees');
		
		$sql = "SELECT s.*, c.name as category_name, r.name as room_name, e.name as employee_name 
				FROM $table_serv s
				LEFT JOIN $table_cat c ON s.category_id = c.id
				LEFT JOIN $table_rooms r ON s.room_id = r.id
				LEFT JOIN $table_emp e ON s.employee_id = e.id
				ORDER BY s.id ASC";
				
		return $wpdb->get_results( $sql, ARRAY_A );
	}
	
	public static function get_bookings_with_relations() {
		global $wpdb;
		$table_book = self::get_table_name('bookings');
		$table_serv = self::get_table_name('services');
		$table_cust = self::get_table_name('customers');
		
		$sql = "SELECT b.*, s.name as service_name, c.first_name, c.last_name, c.email, c.phone 
				FROM $table_book b
				LEFT JOIN $table_serv s ON b.service_id = s.id
				LEFT JOIN $table_cust c ON b.customer_id = c.id
				ORDER BY b.booking_date DESC, b.start_time DESC";
				
		return $wpdb->get_results( $sql, ARRAY_A );
	}

}
