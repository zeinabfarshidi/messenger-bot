<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('plugins_loaded', function() {
    add_action('rest_api_init', function() {
        register_rest_route('telegram-bot/v1', '/webhook', [
            'methods' => 'POST',
            'callback' => function($request) {
                $token = '7681362529:AAHUjV8JgDlNJWjjsnATUjK9Svujcmjmq_8';
                $update = json_decode($request->get_body(), true);

                file_put_contents(
                    WP_CONTENT_DIR . '/telegram-debug.log',
                    date('Y-m-d H:i:s') . ': ' . print_r($update, true) . "\n",
                    FILE_APPEND
                );

                if (isset($update['my_chat_member'])) {
                    $chat = $update['my_chat_member']['chat'];
                    $new_status = $update['my_chat_member']['new_chat_member']['status'];

                    global $wpdb;
                    $table_name = $wpdb->prefix . 'telegram_groups';

                    if ($new_status == 'member' || $new_status == 'administrator') {
                        // ذخیره اطلاعات گروه در دیتابیس
                        $wpdb->replace(
                            $table_name,
                            array(
                                'group_id' => $chat['id'],
                                'group_name' => $chat['title']
                            ),
                            array('%s', '%s')
                        );
                    }
                }
                if (isset($update['message']['text'])) {
                    $chat_id = $update['message']['chat']['id'];
                    $text = $update['message']['text'];

                    if (strpos($text, '/send') === 0) {
                        $parts = explode(' ', $text, 3);
                        if (count($parts) >= 3) {
                            $target_chat = $parts[1];
                            $message = $parts[2];

                            wp_remote_post("https://api.telegram.org/bot{$token}/sendMessage", [
                                'body' => [
                                    'chat_id' => $target_chat,
                                    'text' => $message
                                ]
                            ]);

                            wp_remote_post("https://api.telegram.org/bot{$token}/sendMessage", [
                                'body' => [
                                    'chat_id' => $chat_id,
                                    'text' => "پیام با موفقیت ارسال شد"
                                ]
                            ]);
                        }
                    }

                    if (strpos($text, '/join') === 0) {
                        wp_remote_post("https://api.telegram.org/bot{$token}/sendMessage", [
                            'body' => [
                                'chat_id' => $chat_id,
                                'text' => "دستور join دریافت شد"
                            ]
                        ]);

                        $parts = explode(' ', $text, 2);
                        if (count($parts) == 2) {
                            $group_username = $parts[1];

                            wp_remote_post("https://api.telegram.org/bot{$token}/sendMessage", [
                                'body' => [
                                    'chat_id' => $chat_id,
                                    'text' => "تلاش برای بررسی گروه: {$group_username}"
                                ]
                            ]);

                            $response = wp_remote_get("https://api.telegram.org/bot{$token}/getChat?chat_id=@{$group_username}");
                            $result = json_decode(wp_remote_retrieve_body($response), true);

                            wp_remote_post("https://api.telegram.org/bot{$token}/sendMessage", [
                                'body' => [
                                    'chat_id' => $chat_id,
                                    'text' => "اطلاعات گروه: " . print_r($result, true)
                                ]
                            ]);
                        }
                    }
                }

                return new WP_REST_Response('OK', 200);
            },
            'permission_callback' => '__return_true'
        ]);
    });
});
//تا اینجا ارسال پیام و عضویت در گروه
//----------------------------------------------------------------------------------------------

if (isset($update['my_chat_member'])) {
    $chat = $update['my_chat_member']['chat'];
    $new_status = $update['my_chat_member']['new_chat_member']['status'];

    // اضافه کردن لاگ
    error_log('Telegram Update: ' . print_r($update, true));
    error_log('Chat Info: ' . print_r($chat, true));
    error_log('New Status: ' . $new_status);

    global $wpdb;
    $table_name = $wpdb->prefix . 'telegram_groups';

    if ($new_status == 'member' || $new_status == 'administrator') {
        $result = $wpdb->replace(
            $table_name,
            array(
                'group_id' => $chat['id'],
                'group_name' => $chat['title']
            ),
            array('%s', '%s')
        );
        error_log('Database Insert Result: ' . print_r($result, true));
    }
}

add_action('init', function() {
    error_log('Request to webhook: ' . print_r($_POST, true));
});
