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

/**
 * Begins execution of the plugin.
 */
function run_colosseum_arena_booking() {
	$plugin = new Colosseum_Arena_Booking();
	$plugin->run();
}
run_colosseum_arena_booking();
