<?php
if (!defined('ABSPATH')) {
    exit;
}

define('TELEGRAM_BOT_VERSION', '1.0.0');
define('TELEGRAM_BOT_PATH', plugin_dir_path(__FILE__));
define('TELEGRAM_BOT_URL', plugin_dir_url(__FILE__));
define('BOT_TOKEN', '7681362529:AAHUjV8JgDlNJWjjsnATUjK9Svujcmjmq_8');
define('BOT_USERNAME', '@zf_plugin_bot');
define('SITE_URL', 'https://zfpluginbot.xyz/');

require_once TELEGRAM_BOT_PATH . 'includes/class-telegram-messenger.php';
require_once TELEGRAM_BOT_PATH . 'includes/admin/admin-menus.php';
require_once TELEGRAM_BOT_PATH . 'includes/admin/admin-functions.php';
require_once TELEGRAM_BOT_PATH . 'includes/database/db-functions.php';

class TelegramBot {
    private static $instance = null;
    private $telegram;
    private $namespace = 'telegram-bot/v1';
    private $route = 'webhook';

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('rest_api_init', [$this, 'init_telegram_bot']);
        $this->telegram = new TelegramMessenger(BOT_TOKEN);
    }

    public function init_telegram_bot() {
        register_rest_route($this->namespace, $this->route, [
            'methods' => ['GET', 'POST'],
            'callback' => [$this, 'handle_telegram_webhook'],
            'permission_callback' => '__return_true'
        ]);
    }
}

// Initialize the plugin
function init_telegram_bot() {
    return TelegramBot::get_instance();
}

add_action('init', 'init_telegram_bot');


