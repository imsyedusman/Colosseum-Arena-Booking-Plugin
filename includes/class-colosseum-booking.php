<?php

class Colosseum_Arena_Booking {

	protected $loader;
	protected $plugin_name;
	protected $version;

	public function __construct() {
		if ( defined( 'CAB_VERSION' ) ) {
			$this->version = CAB_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'colosseum-booking';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cab-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cab-db.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-cab-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-cab-public.php';

		$this->loader = new Colosseum_Arena_Booking_Loader();
	}

	private function define_admin_hooks() {
		$plugin_admin = new Colosseum_Arena_Booking_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
		
		// Admin AJAX 
		$this->loader->add_action( 'wp_ajax_cab_ajax', $plugin_admin, 'ajax_handler' );
	}

	private function define_public_hooks() {
		$plugin_public = new Colosseum_Arena_Booking_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		
		$this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );
		
		// Frontend AJAX
		$this->loader->add_action( 'wp_ajax_cab_frontend_ajax', $plugin_public, 'ajax_handler' );
		$this->loader->add_action( 'wp_ajax_nopriv_cab_frontend_ajax', $plugin_public, 'ajax_handler' );
		
		// WooCommerce Integration Hooks
		$this->loader->add_action( 'template_redirect', $plugin_public, 'maybe_add_booking_product_to_cart' );
		$this->loader->add_action( 'woocommerce_order_status_completed', $plugin_public, 'complete_booking_on_payment' );
	}

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}
}
