<?php
$path = dirname(__FILE__);
while (!file_exists($path . '/wp-load.php')) {
    $path = dirname($path);
    if ($path == dirname($path)) {
        die("Could not find wp-load.php");
    }
}
require_once $path . '/wp-load.php';
require_once dirname(__FILE__) . '/includes/class-cab-activator.php';

// Try executing seed
echo "Running seed_data()...\n";
$counts = Colosseum_Arena_Booking_Activator::seed_data();
print_r($counts);
echo "\nChecking DB counts...\n";
global $wpdb;
$services_count = $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . "cab_services");
echo "Total Services in DB: " . $services_count . "\n";
