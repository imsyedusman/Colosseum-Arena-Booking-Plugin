<?php

class Colosseum_Arena_Booking_Public {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/widget.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0', 'all' );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/widget.js', array( 'jquery' ), $this->version, true );
		wp_localize_script( $this->plugin_name, 'cab_public_obj', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'cab_public_nonce' )
		) );
	}

	public function register_shortcodes() {
		add_shortcode( 'colosseum_booking_widget', array( $this, 'display_booking_widget' ) );
	}

	public function display_booking_widget( $atts ) {
		ob_start();
		include plugin_dir_path( __FILE__ ) . 'partials/widget.php';
		return ob_get_clean();
	}

	public function ajax_handler() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cab-frontend-ajax.php';
		CABA_Frontend_Ajax::handle_request();
	}
	
	public function maybe_add_booking_product_to_cart() {
		// WooCommerce integration hook for intercepting "Pay online" flows that added a dummy product
	}
	
	public function complete_booking_on_payment( $order_id ) {
		// Mark booking as paid if WooCommerce order matches
	}
}
