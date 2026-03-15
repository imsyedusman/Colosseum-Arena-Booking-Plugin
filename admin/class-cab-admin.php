<?php

class Colosseum_Arena_Booking_Admin {

	private $plugin_name;
	private $version;

	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function enqueue_styles( $hook_suffix ) {
		// Only load styles on our plugin pages
		if ( strpos( $hook_suffix, 'colosseum-arena-booking' ) === false ) {
			return;
		}

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/admin.css', array(), $this->version, 'all' );
		
		// Load Tailwind via CDN and FontAwesome
		wp_enqueue_script( 'tailwindcss', 'https://cdn.tailwindcss.com', array(), '3.4.1', false );
		wp_enqueue_style( 'fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0', 'all' );
        
		wp_enqueue_style( 'datatables-css', 'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css', array(), '1.13.6', 'all' );
	}

	public function enqueue_scripts( $hook_suffix ) {
		if ( strpos( $hook_suffix, 'colosseum-arena-booking' ) === false ) {
			return;
		}

		wp_enqueue_script( 'datatables-js', 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', array( 'jquery' ), '1.13.6', false );
		wp_enqueue_script( 'sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array(), '11', false );
		
		if ( strpos( $hook_suffix, 'colosseum-arena-booking-calendar' ) !== false ) {
			wp_enqueue_script( 'fullcalendar-js', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js', array(), '6.1.10', false );
		}

		$admin_script_path = plugin_dir_path( __FILE__ ) . 'js/admin.js';
		$admin_script_version = file_exists( $admin_script_path ) ? filemtime( $admin_script_path ) : $this->version;
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery' ), $admin_script_version, false );

		wp_localize_script( $this->plugin_name, 'cab_ajax_obj', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'cab_admin_nonce' )
		) );
	}

	public function add_plugin_admin_menu() {
		add_menu_page(
			'Colosseum Booking',
			'Colosseum Booking',
			'manage_options',
			'colosseum-arena-booking',
			array( $this, 'display_dashboard' ),
			'dashicons-calendar-alt',
			26
		);

		add_submenu_page( 'colosseum-arena-booking', 'Dashboard', 'Dashboard', 'manage_options', 'colosseum-arena-booking', array( $this, 'display_dashboard' ) );
		add_submenu_page( 'colosseum-arena-booking', 'Rezervări', 'Rezervări', 'manage_options', 'colosseum-arena-booking-rezervari', array( $this, 'display_rezervari' ) );
		add_submenu_page( 'colosseum-arena-booking', 'Calendar', 'Calendar', 'manage_options', 'colosseum-arena-booking-calendar', array( $this, 'display_calendar' ) );
		add_submenu_page( 'colosseum-arena-booking', 'Servicii', 'Servicii', 'manage_options', 'colosseum-arena-booking-servicii', array( $this, 'display_servicii' ) );
		add_submenu_page( 'colosseum-arena-booking', 'Categorii', 'Categorii', 'manage_options', 'colosseum-arena-booking-categorii', array( $this, 'display_categorii' ) );
		add_submenu_page( 'colosseum-arena-booking', 'Camere', 'Camere', 'manage_options', 'colosseum-arena-booking-camere', array( $this, 'display_camere' ) );
		add_submenu_page( 'colosseum-arena-booking', 'Angajați', 'Angajați', 'manage_options', 'colosseum-arena-booking-angajati', array( $this, 'display_angajati' ) );
		add_submenu_page( 'colosseum-arena-booking', 'Clienți', 'Clienți', 'manage_options', 'colosseum-arena-booking-clienti', array( $this, 'display_clienti' ) );
		add_submenu_page( 'colosseum-arena-booking', 'Notificări', 'Notificări', 'manage_options', 'colosseum-arena-booking-notificari', array( $this, 'display_notificari' ) );
		add_submenu_page( 'colosseum-arena-booking', 'Setări', 'Setări', 'manage_options', 'colosseum-arena-booking-setari', array( $this, 'display_setari' ) );
	}

	public function display_dashboard() { include plugin_dir_path( __FILE__ ) . 'partials/dashboard.php'; }
	public function display_rezervari() { include plugin_dir_path( __FILE__ ) . 'partials/rezervari.php'; }
	public function display_calendar() { include plugin_dir_path( __FILE__ ) . 'partials/calendar.php'; }
	public function display_servicii() { include plugin_dir_path( __FILE__ ) . 'partials/servicii.php'; }
	public function display_categorii() { include plugin_dir_path( __FILE__ ) . 'partials/categorii.php'; }
	public function display_camere() { include plugin_dir_path( __FILE__ ) . 'partials/camere.php'; }
	public function display_angajati() { include plugin_dir_path( __FILE__ ) . 'partials/angajati.php'; }
	public function display_clienti() { include plugin_dir_path( __FILE__ ) . 'partials/clienti.php'; }
	public function display_notificari() { include plugin_dir_path( __FILE__ ) . 'partials/notificari.php'; }
	public function display_setari() { include plugin_dir_path( __FILE__ ) . 'partials/setari.php'; }

	public function ajax_handler() {
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-cab-ajax.php';
		CABA_Ajax::handle_admin_ajax();
	}
}
