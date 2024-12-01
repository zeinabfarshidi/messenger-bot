<?php
require_once('../../../wp-load.php');

global $wpdb;

$tables = [
    $wpdb->prefix . 'messenger_groups',
    $wpdb->prefix . 'messenger_messages',
    $wpdb->prefix . 'messenger_members',
    $wpdb->prefix . 'messenger_updates',
    $wpdb->prefix . 'messenger_files'
];

foreach ($tables as $table) {
    if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
        echo "جدول $table وجود ندارد<br>";
    } else {
        echo "جدول $table به درستی ایجاد شده است<br>";
    }
}
