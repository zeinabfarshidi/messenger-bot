<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', function() {
    register_rest_route('telegram-bot/v1', '/webhook', [
        'methods' => 'POST',
        'callback' => function($request) {
            $token = '7681362529:AAHUjV8JgDlNJWjjsnATUjK9Svujcmjmq_8';
            $update = json_decode($request->get_body(), true);
            // Log incoming webhook data
            file_put_contents(
                ABSPATH . 'webhook-debug.log',
                date('Y-m-d H:i:s') . ': ' . print_r($update, true) . "\n",
                FILE_APPEND
            );

            if (isset($update['my_chat_member'])) {
                $chat = $update['my_chat_member']['chat'];
                $new_status = $update['my_chat_member']['new_chat_member']['status'];

                global $wpdb;
                $table_name = $wpdb->prefix . 'telegram_groups';

                if ($new_status == 'member' || $new_status == 'administrator') {
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

            // Rest of your existing message handling code...
            if (isset($update['message']['text'])) {
                // Your existing message handling code...
            }

            return new WP_REST_Response('OK', 200);
        },
        'permission_callback' => '__return_true'
    ]);
});
