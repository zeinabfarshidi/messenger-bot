<?php
require_once('../../../wp-load.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// لود کردن فایل اصلی پلاگین
require_once('messenger-bot.php');

try {
    $plugin = messenger_bot();
    echo "پلاگین با موفقیت لود شد<br>";

    // بررسی متدهای اصلی
    if (method_exists($plugin, 'activate')) {
        echo "متد activate وجود دارد<br>";
    }

    // بررسی کلاس‌های وابسته
    if (class_exists('MessengerManager')) {
        echo "کلاس MessengerManager وجود دارد<br>";
    }

    if (interface_exists('MessengerInterface')) {
        echo "اینترفیس MessengerInterface وجود دارد<br>";
    }

} catch (Exception $e) {
    echo "خطا: " . $e->getMessage();
}
