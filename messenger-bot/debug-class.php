<?php
define('WP_DEBUG', true);
define('WP_DEBUG_DISPLAY', true);

require_once('../../../wp-load.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "Step 1: Starting debug<br>";

if (file_exists('includes/class-messenger-manager.php')) {
    echo "Step 2: File exists<br>";
    require_once('includes/class-messenger-manager.php');
    echo "Step 3: File included<br>";

    if (class_exists('MessengerManager')) {
        echo "Step 4: Class exists<br>";
        $manager = MessengerManager::get_instance();
        echo "Step 5: Instance created<br>";
        var_dump($manager);
    } else {
        echo "Error: Class MessengerManager not found<br>";
    }
} else {
    echo "Error: File not found at: " . __DIR__ . '/includes/class-messenger-manager.php';
}
