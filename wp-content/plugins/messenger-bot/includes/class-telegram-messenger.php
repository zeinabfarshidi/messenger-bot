<?php
if (!defined('ABSPATH')) {
    exit;
}

class TelegramMessenger implements MessengerInterface {
    private $token;
    private $api_url = 'https://api.telegram.org/bot';
    private $db;

    public function __construct($token) {
        global $wpdb;
        $this->token = $token;
        $this->db = $wpdb;
    }

    public function get_name() {
        return 'telegram';
    }

    public function get_version() {
        return '1.0.0';
    }

    public function get_status() {
        $response = $this->make_request('getMe');
        return isset($response['ok']) ? 'active' : 'inactive';
    }

    public function create_group($group_data) {
        $response = $this->make_request('createChat', [
            'title' => $group_data['title'],
            'type' => $group_data['type'] ?? 'group'
        ]);

        if ($response['ok']) {
            return [
                'id' => $response['result']['id'],
                'title' => $response['result']['title'],
                'type' => $group_data['type'] ?? 'group'
            ];
        }
        return false;
    }

    public function update_group($group_id, $group_data) {
        $response = $this->make_request('setChatTitle', [
            'chat_id' => $group_id,
            'title' => $group_data['title']
        ]);
        return $response['ok'] ?? false;
    }

    public function delete_group($group_id) {
        $response = $this->make_request('leaveChat', [
            'chat_id' => $group_id
        ]);
        return $response['ok'] ?? false;
    }

    public function get_group_stats($group_id) {
        $response = $this->make_request('getChatMembersCount', [
            'chat_id' => $group_id
        ]);
        return $response['ok'] ? $response['result'] : false;
    }

    public function check_group_status($group_id) {
        $response = $this->make_request('getChat', [
            'chat_id' => $group_id
        ]);
        return $response['ok'] ? 'active' : 'inactive';
    }

    public function save_group_to_db($group_data) {
        return $this->db->insert(
            $this->db->prefix . 'messenger_groups',
            [
                'messenger_name' => $this->get_name(),
                'group_id' => $group_data['id'],
                'group_title' => $group_data['title'],
                'group_type' => $group_data['type']
            ],
            ['%s', '%s', '%s', '%s']
        );
    }

    public function update_group_in_db($group_id, $group_data) {
        return $this->db->update(
            $this->db->prefix . 'messenger_groups',
            ['group_title' => $group_data['title']],
            ['group_id' => $group_id],
            ['%s'],
            ['%s']
        );
    }

    public function delete_group_from_db($group_id) {
        return $this->db->delete(
            $this->db->prefix . 'messenger_groups',
            ['group_id' => $group_id],
            ['%s']
        );
    }

    public function get_group_members($group_id) {
        $response = $this->make_request('getChatAdministrators', [
            'chat_id' => $group_id
        ]);
        return $response['ok'] ? $response['result'] : [];
    }

    public function add_member($group_id, $user_id) {
        $response = $this->make_request('inviteChatMember', [
            'chat_id' => $group_id,
            'user_id' => $user_id
        ]);
        return $response['ok'] ?? false;
    }

    public function remove_member($group_id, $user_id) {
        $response = $this->make_request('kickChatMember', [
            'chat_id' => $group_id,
            'user_id' => $user_id
        ]);
        return $response['ok'] ?? false;
    }

    public function save_member_to_db($member_data) {
        return $this->db->insert(
            $this->db->prefix . 'messenger_members',
            $member_data,
            ['%s', '%s', '%s']
        );
    }

    public function update_member_in_db($member_id, $member_data) {
        return $this->db->update(
            $this->db->prefix . 'messenger_members',
            $member_data,
            ['id' => $member_id],
            ['%s'],
            ['%d']
        );
    }

    public function send_message($chat_id, $message, $options = []) {
        $params = array_merge([
            'chat_id' => $chat_id,
            'text' => $message,
            'parse_mode' => 'HTML'
        ], $options);

        $response = $this->make_request('sendMessage', $params);
        return $response['ok'] ? $response['result'] : false;
    }

    public function edit_message($chat_id, $message_id, $new_message) {
        $response = $this->make_request('editMessageText', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $new_message,
            'parse_mode' => 'HTML'
        ]);
        return $response['ok'] ?? false;
    }

    public function delete_message($chat_id, $message_id) {
        $response = $this->make_request('deleteMessage', [
            'chat_id' => $chat_id,
            'message_id' => $message_id
        ]);
        return $response['ok'] ?? false;
    }

    public function log_message($message_data) {
        return $this->db->insert(
            $this->db->prefix . 'messenger_messages',
            $message_data,
            ['%s', '%s', '%s', '%s']
        );
    }

    public function get_message_history($chat_id) {
        return $this->db->get_results(
            $this->db->prepare(
                "SELECT * FROM {$this->db->prefix}messenger_messages 
                WHERE chat_id = %s ORDER BY sent_at DESC",
                $chat_id
            )
        );
    }

    public function get_updates($offset = null) {
        $params = ['timeout' => 30];
        if ($offset) {
            $params['offset'] = $offset;
        }
        return $this->make_request('getUpdates', $params);
    }

    public function save_updates_to_db($updates) {
        foreach ($updates as $update) {
            $this->db->insert(
                $this->db->prefix . 'messenger_updates',
                [
                    'update_id' => $update['update_id'],
                    'update_type' => $this->get_update_type($update),
                    'update_data' => json_encode($update),
                    'created_at' => current_time('mysql')
                ],
                ['%d', '%s', '%s', '%s']
            );
        }
    }

    public function send_file($chat_id, $file_path, $caption = '') {
        $method = $this->get_file_method($file_path);

        $params = [
            'chat_id' => $chat_id,
            $method => new CURLFile($file_path),
            'caption' => $caption
        ];

        $response = $this->make_request($method, $params, true);
        return $response['ok'] ? $response['result'] : false;
    }

    public function download_file($file_id) {
        $response = $this->make_request('getFile', [
            'file_id' => $file_id
        ]);

        if ($response['ok']) {
            $file_path = $response['result']['file_path'];
            $download_url = "https://api.telegram.org/file/bot{$this->token}/{$file_path}";
            return wp_remote_get($download_url);
        }
        return false;
    }

    public function save_file_metadata($file_data) {
        return $this->db->insert(
            $this->db->prefix . 'messenger_files',
            $file_data,
            ['%s', '%s', '%s', '%s']
        );
    }

    private function make_request($method, $params = [], $is_file = false) {
        $url = $this->api_url . $this->token . '/' . $method;

        $args = [
            'timeout' => 30,
            'headers' => []
        ];

        if ($is_file) {
            $args['headers']['Content-Type'] = 'multipart/form-data';
        } else {
            $args['headers']['Content-Type'] = 'application/json';
            $params = json_encode($params);
        }

        $args['body'] = $params;

        $response = wp_remote_post($url, $args);

        if (is_wp_error($response)) {
            error_log('Telegram API Error: ' . $response->get_error_message());
            return ['ok' => false, 'error' => $response->get_error_message()];
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    private function get_file_method($file_path) {
        $extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));

        $methods = [
            'jpg' => 'sendPhoto',
            'jpeg' => 'sendPhoto',
            'png' => 'sendPhoto',
            'gif' => 'sendAnimation',
            'mp4' => 'sendVideo',
            'mp3' => 'sendAudio',
            'pdf' => 'sendDocument'
        ];

        return $methods[$extension] ?? 'sendDocument';
    }

    private function get_update_type($update) {
        if (isset($update['message'])) return 'message';
        if (isset($update['edited_message'])) return 'edited_message';
        if (isset($update['channel_post'])) return 'channel_post';
        if (isset($update['callback_query'])) return 'callback_query';
        return 'unknown';
    }

    public function set_webhook($url) {
        $api_url = "https://api.telegram.org/bot" . $this->token . "/setWebhook";
        $params = [
            'url' => $url,
            'allowed_updates' => ['message', 'callback_query']
        ];

        $response = wp_remote_post($api_url, [
            'body' => $params,
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            return ['ok' => false, 'description' => $response->get_error_message()];
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    public function join_chat($invite_link) {
        $api_url = "https://api.telegram.org/bot" . $this->token . "/joinChatByInviteLink";
        $params = [
            'invite_link' => $invite_link
        ];

        $response = wp_remote_post($api_url, [
            'body' => json_encode($params),
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'timeout' => 30
        ]);

        if (is_wp_error($response)) {
            error_log('Telegram API Error: ' . $response->get_error_message());
            return ['ok' => false, 'description' => $response->get_error_message()];
        }

        $result = json_decode(wp_remote_retrieve_body($response), true);
        error_log('Telegram API Response: ' . print_r($result, true));

        return $result;
    }



}
