<?php
// Find wp-load.php
$path = dirname(__FILE__);
while (!file_exists($path . '/wp-load.php')) {
    $path = dirname($path);
    if ($path == dirname($path)) {
        die("Could not find wp-load.php");
    }
}
require_once $path . '/wp-load.php';
require_once dirname(__FILE__) . '/includes/class-cab-db.php';
require_once dirname(__FILE__) . '/includes/class-cab-ajax.php';

$seed_file = dirname(__FILE__) . '/colosseum_seed.json';
if (!file_exists($seed_file)) {
    die("Seed file not found: $seed_file");
}

$seed_json = file_get_contents($seed_file);
$payload_array = json_decode($seed_json, true);

if (!$payload_array) {
    die("Failed to decode seed json in test script");
}

// Simulate the POST payload
$_POST['payload'] = $payload_array;

// Because it's a private method and check_ajax_referer is in handle_admin_ajax,
// we will use Reflection to invoke the private import_seed_json directly.
$method = new ReflectionMethod('CABA_Ajax', 'import_seed_json');
$method->setAccessible(true);

echo "Starting import test...\n";
try {
    $method->invoke(null);
} catch (Throwable $e) {
    echo "Error caught: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
