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
        wp_enqueue_style( 'flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', array(), '4.6.13', 'all' );
	}

	public function enqueue_scripts() {
        wp_enqueue_script( 'flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr', array(), '4.6.13', true );
        wp_enqueue_script( 'flatpickr-ro', 'https://npmcdn.com/flatpickr/dist/l10n/ro.js', array('flatpickr-js'), '4.6.13', true );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/widget.js', array( 'jquery', 'flatpickr-js' ), $this->version, true );
		wp_localize_script( $this->plugin_name, 'cab_public_obj', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'cab_public_nonce' )
		) );
	}

	public function register_shortcodes() {
		add_shortcode( 'colosseum_booking_widget', array( $this, 'display_booking_widget' ) );
	}

	public function display_booking_widget( $atts ) {
		// Ensure the hidden product exists before showing the widget (if WC is active)
		$this->ensure_booking_product_exists();
		
		ob_start();
		include plugin_dir_path( __FILE__ ) . 'partials/widget.php';
		return ob_get_clean();
	}

	public function ajax_handler() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cab-frontend-ajax.php';
		CABA_Frontend_Ajax::handle_request();
	}
	
	private function ensure_booking_product_exists() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		$product_id = get_option( 'cab_wc_product_id', 0 );
		
		// If product_id exists, check if it's still a valid product
		if ( $product_id ) {
			$product = wc_get_product( $product_id );
			if ( $product ) {
				return; // Already exists and valid
			}
		}

		// Create the hidden product
		$post_id = wp_insert_post( array(
			'post_title'   => 'Rezervare Colosseum',
			'post_content' => 'Acest produs este folosit automat de sistemul de rezervări.',
			'post_status'  => 'publish',
			'post_type'    => 'product',
		) );

		if ( $post_id ) {
			$product = new WC_Product_Simple( $post_id );
			$product->set_regular_price( '1.00' ); // Fallback price
			$product->set_catalog_visibility( 'hidden' );
			$product->set_virtual( true );
			$product->set_sold_individually( true );
			$product->save();

			update_option( 'cab_wc_product_id', $post_id );
			update_option( 'cab_wc_enabled', 1 ); // Auto-enable if we created it
		}
	}
	
	public function maybe_add_booking_product_to_cart() {
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'set_custom_cart_item_price' ), 9999, 1 );
		add_filter( 'woocommerce_get_item_data', array( $this, 'display_booking_data_in_cart' ), 10, 2 );
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'save_booking_id_to_order_item' ), 10, 4 );
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'get_cart_item_from_session' ), 10, 3 );
	}

	public function get_cart_item_from_session( $cart_item, $values, $cart_item_key ) {
		if ( isset( $values['cab_custom_price'] ) ) {
			$cart_item['cab_custom_price'] = $values['cab_custom_price'];
		}
		if ( isset( $values['cab_service_name'] ) ) {
			$cart_item['cab_service_name'] = $values['cab_service_name'];
		}
		if ( isset( $values['cab_booking_id'] ) ) {
			$cart_item['cab_booking_id'] = $values['cab_booking_id'];
		}
		return $cart_item;
	}

	public function set_custom_cart_item_price( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;
		if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) return;

		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			if ( isset( $cart_item['cab_custom_price'] ) ) {
				$cart_item['data']->set_price( $cart_item['cab_custom_price'] );
			}
		}
	}

	public function display_booking_data_in_cart( $item_data, $cart_item ) {
		if ( isset( $cart_item['cab_service_name'] ) ) {
			$item_data[] = array(
				'key'   => 'Serviciu',
				'value' => $cart_item['cab_service_name']
			);
		}
		return $item_data;
	}

	public function save_booking_id_to_order_item( $item, $cart_item_key, $values, $order ) {
		if ( isset( $values['cab_booking_id'] ) ) {
			$item->update_meta_data( 'cab_booking_id', $values['cab_booking_id'] );
		}
	}
	
	/**
	 * When a WooCommerce order is completed, mark the corresponding booking as 'confirmed'.
	 */
	public function complete_booking_on_payment( $order_id ) {
		$order = wc_get_order( $order_id );
		foreach ( $order->get_items() as $item_id => $item ) {
			$booking_id = $item->get_meta( 'cab_booking_id' );
			if ( $booking_id ) {
				global $wpdb;
				$table = $wpdb->prefix . 'cab_bookings';
				$wpdb->update( $table, array( 'status' => 'confirmed', 'wc_order_id' => $order_id ), array( 'id' => $booking_id ) );
			}
		}
	}
}
