<?php
if (!defined('ABSPATH')) {
    exit;
}

class Messenger_Settings_Controller {
    private $settings;
    private $option_name = 'messenger_settings';

    public function __construct() {
        add_action('admin_init', [$this, 'register_settings']);
        $this->settings = get_option($this->option_name, $this->get_default_settings());
    }

    public function display() {
        $settings = $this->settings;
        include MESSENGER_BOT_PLUGIN_DIR . 'admin/views/settings.php';
    }

    public function register_settings() {
        register_setting(
            'messenger_settings_group',
            $this->option_name,
            [$this, 'sanitize_settings']
        );
    }

    private function get_default_settings() {
        return [
            'daily_message_limit' => 1000,
            'message_interval' => 1,
            'enable_logging' => 1,
            'telegram_token' => '',
            'telegram_status' => 'disabled',
            'bale_token' => '',
            'bale_status' => 'disabled',
            'soroush_token' => '',
            'soroush_status' => 'disabled',
            'debug_mode' => 0,
            'log_path' => WP_CONTENT_DIR . '/messenger-logs',
            'log_cleanup_days' => '14'
        ];
    }

    public function sanitize_settings($input) {
        $sanitized = [];

        // تنظیمات عمومی
        $sanitized['daily_message_limit'] = absint($input['daily_message_limit']);
        $sanitized['message_interval'] = absint($input['message_interval']);
        $sanitized['enable_logging'] = isset($input['enable_logging']) ? 1 : 0;

        // تنظیمات پیام‌رسان‌ها
        $messengers = ['telegram', 'bale', 'soroush'];
        foreach ($messengers as $messenger) {
            $sanitized[$messenger . '_token'] = sanitize_text_field($input[$messenger . '_token']);
            $sanitized[$messenger . '_status'] = in_array($input[$messenger . '_status'], ['enabled', 'disabled'])
                ? $input[$messenger . '_status']
                : 'disabled';
        }

        // تنظیمات پیشرفته
        $sanitized['debug_mode'] = isset($input['debug_mode']) ? 1 : 0;
        $sanitized['log_path'] = sanitize_text_field($input['log_path']);
        $sanitized['log_cleanup_days'] = in_array($input['log_cleanup_days'], ['7', '14', '30', '0'])
            ? $input['log_cleanup_days']
            : '14';

        // بررسی و ایجاد مسیر لاگ‌ها
        if (!empty($sanitized['log_path'])) {
            if (!file_exists($sanitized['log_path'])) {
                wp_mkdir_p($sanitized['log_path']);
            }
        }

        return $sanitized;
    }

    public function validate_messenger_tokens() {
        $messengers = ['telegram', 'bale', 'soroush'];
        $results = [];

        foreach ($messengers as $messenger) {
            if (!empty($this->settings[$messenger . '_token']) &&
                $this->settings[$messenger . '_status'] === 'enabled') {
                $results[$messenger] = $this->test_messenger_connection($messenger);
            }
        }

        return $results;
    }

    private function test_messenger_connection($messenger) {
        $token = $this->settings[$messenger . '_token'];

        // اینجا کد تست اتصال به API هر پیام‌رسان را می‌نویسیم
        switch ($messenger) {
            case 'telegram':
                return $this->test_telegram_connection($token);
            case 'bale':
                return $this->test_bale_connection($token);
            case 'soroush':
                return $this->test_soroush_connection($token);
        }

        return false;
    }

    private function test_telegram_connection($token) {
        $response = wp_remote_get("https://api.telegram.org/bot{$token}/getMe");
        return !is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
    }

    private function test_bale_connection($token) {
        // کد تست اتصال به API بله
        return true;
    }

    private function test_soroush_connection($token) {
        // کد تست اتصال به API سروش
        return true;
    }
}
