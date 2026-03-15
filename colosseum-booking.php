<?php
/**
 * Plugin Name:       Colosseum Arena Booking System
 * Description:       A complete booking plugin for Colosseum Laser Tag replacing Amelia. Manages birthday party packages with specific room constraints/schedules, WooCommerce integration, and robust Admin CRUD.
 * Version:           1.0.0
 * Author:            Syed Usman
 * Text Domain:       colosseum-booking
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'CAB_VERSION', '1.0.0' );
define( 'CAB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CAB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_colosseum_arena_booking() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cab-activator.php';
	Colosseum_Arena_Booking_Activator::activate();
	cab_schedule_expiration_cron();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_colosseum_arena_booking() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-cab-deactivator.php';
	Colosseum_Arena_Booking_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_colosseum_arena_booking' );
register_deactivation_hook( __FILE__, 'deactivate_colosseum_arena_booking' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-colosseum-booking.php';

function cab_admin_notice_no_services() {
    global $wpdb;
    $table_services = $wpdb->prefix . 'cab_services';
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_services");
    if ( $count == 0 ) {
		$settings_url = admin_url('admin.php?page=colosseum-arena-booking-setari');
        echo '<div class="notice notice-error is-dismissible" style="padding: 15px; border-left-color: #ef4444; background: #fef2f2; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">';
        echo '<p style="font-size: 16px; margin: 0; color: #991b1b;"><strong>Atenție!</strong> Nu există servicii de rezervare în sistem. Formularul de rezervare nu va funcționa corect pe site.</p>';
        echo '<p style="margin-top: 10px; margin-bottom: 0;"><a href="'. esc_url($settings_url) .'" class="button button-primary" style="background: #1d4ed8; border-color: #1e3a8a;">Configurează sau Adaugă date demo</a></p>';
        echo '</div>';
    }
}
add_action( 'admin_notices', 'cab_admin_notice_no_services' );

function cab_cron_schedules( $schedules ) {
    if ( ! isset( $schedules['every_five_minutes'] ) ) {
        $schedules['every_five_minutes'] = array(
            'interval' => 300,
            'display'  => 'La fiecare 5 minute'
        );
    }
    return $schedules;
}
add_filter( 'cron_schedules', 'cab_cron_schedules' );

function cab_schedule_expiration_cron() {
    if ( ! wp_next_scheduled( 'cab_expire_pending_bookings_cron' ) ) {
        wp_schedule_event( time(), 'every_five_minutes', 'cab_expire_pending_bookings_cron' );
    }
}
add_action( 'init', 'cab_schedule_expiration_cron' );

function cab_run_expiration_cron() {
    global $wpdb;
    $table_bookings = $wpdb->prefix . 'cab_bookings';

    $cutoff = gmdate( 'Y-m-d H:i:s', time() - ( 15 * MINUTE_IN_SECONDS ) );
    $wpdb->query(
        $wpdb->prepare(
            "UPDATE $table_bookings
            SET status = 'expired'
            WHERE status = 'pending_payment_online'
            AND created_at < %s",
            $cutoff
        )
    );
}
add_action( 'cab_expire_pending_bookings_cron', 'cab_run_expiration_cron' );

/**
 * Begins execution of the plugin.
 */
function run_colosseum_arena_booking() {
	$plugin = new Colosseum_Arena_Booking();
	$plugin->run();
}
run_colosseum_arena_booking();
