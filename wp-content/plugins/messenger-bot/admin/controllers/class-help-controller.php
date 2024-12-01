<?php
if (!defined('ABSPATH')) {
    exit;
}

class Messenger_Help_Controller {
    public function display() {
        // دریافت اطلاعات نسخه پلاگین
        $plugin_data = get_plugin_data(MESSENGER_BOT_PLUGIN_FILE);
        $version = $plugin_data['Version'];

        // دریافت وضعیت سیستم
        $system_status = $this->get_system_status();

        // دریافت آخرین خطاها
        $recent_errors = $this->get_recent_errors();

        // نمایش صفحه راهنما
        include MESSENGER_BOT_PLUGIN_DIR . 'admin/views/help.php';
    }

    private function get_system_status() {
        return [
            'php_version' => PHP_VERSION,
            'wp_version' => get_bloginfo('version'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'php_extensions' => get_loaded_extensions(),
            'is_ssl' => is_ssl(),
            'is_writable_logs' => wp_is_writable(MESSENGER_BOT_PLUGIN_DIR . 'logs'),
            'cron_status' => defined('DISABLE_WP_CRON') && DISABLE_WP_CRON ? 'disabled' : 'enabled'
        ];
    }

    private function get_recent_errors() {
        global $wpdb;

        return $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}messenger_logs 
             WHERE level = 'error' 
             ORDER BY time DESC 
             LIMIT 5"
        );
    }

    public function register_help_tabs() {
        $screen = get_current_screen();

        // تب راهنمای سریع
        $screen->add_help_tab([
            'id' => 'messenger-quick-start',
            'title' => 'راهنمای سریع',
            'callback' => [$this, 'render_quick_start_tab']
        ]);

        // تب سوالات متداول
        $screen->add_help_tab([
            'id' => 'messenger-faq',
            'title' => 'سوالات متداول',
            'callback' => [$this, 'render_faq_tab']
        ]);

        // تب رفع اشکال
        $screen->add_help_tab([
            'id' => 'messenger-troubleshooting',
            'title' => 'رفع اشکال',
            'callback' => [$this, 'render_troubleshooting_tab']
        ]);

        // تب پشتیبانی
        $screen->add_help_tab([
            'id' => 'messenger-support',
            'title' => 'پشتیبانی',
            'callback' => [$this, 'render_support_tab']
        ]);
    }

    public function render_quick_start_tab() {
        include MESSENGER_BOT_PLUGIN_DIR . 'admin/views/help-tabs/quick-start.php';
    }

    public function render_faq_tab() {
        include MESSENGER_BOT_PLUGIN_DIR . 'admin/views/help-tabs/faq.php';
    }

    public function render_troubleshooting_tab() {
        include MESSENGER_BOT_PLUGIN_DIR . 'admin/views/help-tabs/troubleshooting.php';
    }

    public function render_support_tab() {
        include MESSENGER_BOT_PLUGIN_DIR . 'admin/views/help-tabs/support.php';
    }
}
