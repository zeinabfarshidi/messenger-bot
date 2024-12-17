<?php
/*
Plugin Name: MessengerBot
Plugin URI: https://zfpluginbot.xyz
Description: افزونه حرفه‌ای مدیریت پیام‌رسان‌های مختلف در وردپرس
Version: 1.0.0
Author: Zeynab Farshidi
Author URI: https://zfpluginbot.xyz
Text Domain: messenger-bot
Domain Path: /languages
License: GPL v2 or later
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class MessengerBot {
    private static $instance = null;
    private $messenger_manager;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}