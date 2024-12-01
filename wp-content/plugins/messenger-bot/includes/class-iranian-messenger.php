<?php
if (!defined('ABSPATH')) {
    exit;
}

class IranianMessenger implements MessengerInterface {
    private $messenger_type;
    private $api_token;
    private $base_url;
    private $db;

    public function __construct($messenger_type = 'bale') {
        global $wpdb;
        $this->db = $wpdb;
        $this->messenger_type = $messenger_type;
        $this->api_token = $this->get_api_token();
        $this->set_base_url();
    }

    private function get_api_token() {
        return get_option($this->messenger_type . '_bot_token');
    }

    private function set_base_url() {
        $urls = [
            'bale' => 'https://tapi.bale.ai/bot',
            'soroush' => 'https://bot.splus.ir/bot',
            'eitaa' => 'https://eitaa.com/api/bot',
            'gap' => 'https://api.gap.im/bot'
        ];
        $this->base_url = $urls[$this->messenger_type] ?? $urls['bale'];
    }

    public function get_name() {
        return $this->messenger_type;
    }

    public function get_version() {
        return '1.0.0';
    }

    public function get_status() {
        $response = $this->make_request('getMe');
        return isset($response['ok']) ? 'active' : 'inactive';
    }

    public function create_group($group_data) {
        $response = $this->make_request('createGroup', [
            'title' => $group_data['title'],
            'description' => $group_data['description'] ?? ''
        ]);

        if ($response['ok']) {
            $this->save_group_to_db([
                'id' => $response['result']['id'],
                'title' => $group_data['title'],
                'type' => 'group'
            ]);
            return $response['result'];
        }
        return false;
    }

    public function update_group($group_id, $group_data) {
        $response = $this->make_request('setGroupTitle', [
            'chat_id' => $group_id,
            'title' => $group_data['title']
        ]);

        if ($response['ok']) {
            $this->update_group_in_db($group_id, $group_data);
        }
        return $response['ok'] ?? false;
    }

    public function delete_group($group_id) {
        $response = $this->make_request('deleteGroup', [
            'chat_id' => $group_id
        ]);

        if ($response['ok']) {
            $this->delete_group_from_db($group_id);
        }
        return $response['ok'] ?? false;
    }

    public function get_group_stats($group_id) {
        $response = $this->make_request('getGroupStats', [
            'chat_id' => $group_id
        ]);
        return $response['ok'] ? $response['result'] : false;
    }

    public function check_group_status($group_id) {
        $response = $this->make_request('getGroup', [
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
        $response = $this->make_request('getGroupMembers', [
            'chat_id' => $group_id
        ]);
        return $response['ok'] ? $response['result'] : [];
    }

    public function add_member($group_id, $user_id) {
        $response = $this->make_request('addGroupMember', [
            'chat_id' => $group_id,
            'user_id' => $user_id
        ]);

        if ($response['ok']) {
            $this->save_member_to_db([
                'group_id' => $group_id,
                'user_id' => $user_id,
                'status' => 'active'
            ]);
        }
        return $response['ok'] ?? false;
    }

    public function remove_member($group_id, $user_id) {
        $response = $this->make_request('removeGroupMember', [
            'chat_id' => $group_id,
            'user_id' => $user_id
        ]);

        if ($response['ok']) {
            $this->update_member_in_db($user_id, [
                'status' => 'removed'
            ]);
        }
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

        if ($response['ok']) {
            $this->log_message([
                'chat_id' => $chat_id,
                'message' => $message,
                'status' => 'sent'
            ]);
        }
        return $response['ok'] ? $response['result'] : false;
    }

    public function edit_message($chat_id, $message_id, $new_message) {
        $response = $this->make_request('editMessage', [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $new_message
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
            ['%s', '%s', '%s']
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
        $params = [
            'chat_id' => $chat_id,
            'file' => new CURLFile($file_path),
            'caption' => $caption
        ];

        $response = $this->make_request('sendFile', $params, true);

        if ($response['ok']) {
            $this->save_file_metadata([
                'chat_id' => $chat_id,
                'file_path' => $file_path,
                'file_type' => mime_content_type($file_path),
                'status' => 'sent'
            ]);
        }
        return $response['ok'] ? $response['result'] : false;
    }

    public function download_file($file_id) {
        $response = $this->make_request('getFile', [
            'file_id' => $file_id
        ]);

        if ($response['ok']) {
            $file_path = $response['result']['file_path'];
            $download_url = "{$this->base_url}/file/{$this->api_token}/{$file_path}";
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
        $url = $this->base_url . $this->api_token . '/' . $method;

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
            error_log($this->messenger_type . ' API Error: ' . $response->get_error_message());
            return ['ok' => false, 'error' => $response->get_error_message()];
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    private function get_update_type($update) {
        if (isset($update['message'])) return 'message';
        if (isset($update['edited_message'])) return 'edited_message';
        if (isset($update['channel_post'])) return 'channel_post';
        if (isset($update['callback_query'])) return 'callback_query';
        return 'unknown';
    }
}
