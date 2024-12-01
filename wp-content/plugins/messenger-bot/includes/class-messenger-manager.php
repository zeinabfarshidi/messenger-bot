<?php
class MessengerManager {
    private $db;
    private static $instance = null;

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get_all_groups() {
        return $this->db->get_results(
            "SELECT * FROM {$this->db->prefix}messenger_groups ORDER BY created_at DESC"
        );
    }

    public function save_group_to_db($group_data) {
        return $this->db->insert(
            $this->db->prefix . 'messenger_groups',
            array(
                'messenger_name' => $group_data['messenger_name'],
                'group_id' => $group_data['group_id'],
                'group_title' => $group_data['group_title'],
                'group_type' => $group_data['group_type'],
                'member_count' => $group_data['member_count'],
                'status' => $group_data['status']
            ),
            array('%s', '%s', '%s', '%s', '%d', '%s')
        );
    }

    public function register_messenger($messenger_instance) {
        if (!isset($this->messengers)) {
            $this->messengers = array();
        }
        $messenger_name = $messenger_instance->get_name();
        $this->messengers[$messenger_name] = $messenger_instance;
        return true;
    }

    public function delete_group($group_id) {
        return $this->db->delete(
            $this->db->prefix . 'messenger_groups',
            array('id' => $group_id),
            array('%d')
        );
    }

}
