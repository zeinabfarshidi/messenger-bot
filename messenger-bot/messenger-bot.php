<?php
/*
Plugin Name: Messenger Bot
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

// Define plugin constants
define('MESSENGER_BOT_VERSION', '1.0.0');
define('MESSENGER_BOT_PATH', plugin_dir_path(__FILE__));
define('MESSENGER_BOT_URL', plugin_dir_url(__FILE__));
define('MESSENGER_BOT_FILE', __FILE__);
define('BOT_TOKEN', '7929153006:AAFVnLnb-3Vsqz9FYvIZgQh-5NWV1ED5qW0');

// Load required files
require_once MESSENGER_BOT_PATH . 'includes/class-messenger-manager.php';
require_once MESSENGER_BOT_PATH . 'includes/interface-messenger.php';
require_once MESSENGER_BOT_PATH . 'includes/class-telegram-messenger.php';
require_once MESSENGER_BOT_PATH . 'includes/class-iranian-messenger.php';

// Main plugin class
class Messenger_Bot {
    private static $instance = null;
    private $messenger_manager;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
        $this->init_messenger_manager();
    }

    private function init_hooks() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'load_admin_scripts']);
        add_action('messenger_bot_daily_tasks', [$this, 'do_daily_tasks']);

        register_activation_hook(MESSENGER_BOT_FILE, [$this, 'activate']);
        register_deactivation_hook(MESSENGER_BOT_FILE, [$this, 'deactivate']);
    }

    private function init_messenger_manager() {
        $this->messenger_manager = new MessengerManager();

        // Register messengers
        $telegram = new TelegramMessenger(BOT_TOKEN);

        // فعلاً این دو خط را کامنت می‌کنیم
        // $iranian = new IranianMessenger();
        // $this->messenger_manager->register_messenger($iranian);

        $this->messenger_manager->register_messenger($telegram);
    }


    public function add_admin_menu() {
        add_menu_page(
            'مدیریت پیام‌رسان‌ها',
            'پیام‌رسان‌ها',
            'manage_options',
            'messenger-manager',
            [$this, 'display_admin_page'],
            'dashicons-format-chat'
        );
    }

    public function display_admin_page() {
        include MESSENGER_BOT_PATH . 'admin/messenger-page.php';
    }

    public function load_admin_scripts($hook) {
        if ('toplevel_page_messenger-manager' !== $hook) {
            return;
        }
        wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], '3.7.0', true);
    }

    public function do_daily_tasks() {
        $this->update_groups_statistics();
        $this->cleanup_old_messages();
        $this->send_admin_report();
    }

    private function update_groups_statistics() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'telegram_groups';
        $groups = $wpdb->get_results("SELECT * FROM $table_name");

        foreach ($groups as $group) {
            $new_stats = $this->get_group_activity_stats($group->group_id);
            $this->update_group_statistics($group->id, $new_stats);
        }
    }

    private function cleanup_old_messages() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'telegram_messages';
        $thirty_days_ago = date('Y-m-d H:i:s', strtotime('-30 days'));

        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table_name WHERE created_at < %s",
            $thirty_days_ago
        ));

        $wpdb->query("OPTIMIZE TABLE $table_name");

        $deleted_count = $wpdb->rows_affected;
        update_option('last_cleanup_count', $deleted_count);
        update_option('last_cleanup_date', current_time('mysql'));

        error_log('پاکسازی پیام‌های قدیمی: ' . $deleted_count . ' پیام حذف شد.');
    }

    private function send_admin_report() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'telegram_groups';

        $total_groups = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        $active_groups = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE status = 'active'");

        $report = "گزارش روزانه پیام‌رسان‌ها\n\n";
        $report .= "تعداد کل گروه‌ها: " . $total_groups . "\n";
        $report .= "گروه‌های فعال: " . $active_groups . "\n";
        $report .= "تاریخ گزارش: " . date_i18n('Y-m-d H:i:s') . "\n";

        $admin_email = get_option('admin_email');
        $subject = 'گزارش روزانه پیام‌رسان‌ها - ' . get_bloginfo('name');

        wp_mail($admin_email, $subject, $report);
        error_log('گزارش روزانه ارسال شد: ' . date('Y-m-d H:i:s'));
    }

    public function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = [
            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}messenger_groups (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            messenger_name varchar(50) NOT NULL,
            group_id varchar(100) NOT NULL,
            group_title varchar(255) NOT NULL,
            group_type varchar(20) DEFAULT 'public',
            member_count int DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            status varchar(20) DEFAULT 'active',
            PRIMARY KEY  (id),
            UNIQUE KEY group_unique (messenger_name, group_id)
        ) $charset_collate;",

            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}messenger_messages (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            messenger_name varchar(50) NOT NULL,
            chat_id varchar(100) NOT NULL,
            message_id varchar(100) NOT NULL,
            message_type varchar(20) DEFAULT 'text',
            message_content text NOT NULL,
            sent_at datetime DEFAULT CURRENT_TIMESTAMP,
            status varchar(20) DEFAULT 'sent',
            PRIMARY KEY  (id)
        ) $charset_collate;",

            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}messenger_members (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            group_id bigint(20) NOT NULL,
            user_id varchar(100) NOT NULL,
            username varchar(255),
            joined_at datetime DEFAULT CURRENT_TIMESTAMP,
            status varchar(20) DEFAULT 'active',
            PRIMARY KEY  (id)
        ) $charset_collate;",

            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}messenger_updates (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            update_id bigint(20) NOT NULL,
            update_type varchar(50) NOT NULL,
            update_data text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;",

            "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}messenger_files (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            file_id varchar(100) NOT NULL,
            file_type varchar(50) NOT NULL,
            file_path text NOT NULL,
            uploaded_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charset_collate;"
        ];

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        foreach ($sql as $query) {
            dbDelta($query);
        }

        add_option('messenger_bot_version', MESSENGER_BOT_VERSION);
    }

    public function deactivate() {
        delete_option('messenger_bot_temp_data');
        wp_clear_scheduled_hook('messenger_bot_daily_tasks');
        flush_rewrite_rules();
    }
}

// Initialize plugin
function messenger_bot() {
    return Messenger_Bot::get_instance();
}

// Start the plugin
messenger_bot();
