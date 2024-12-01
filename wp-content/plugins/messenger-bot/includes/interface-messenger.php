<?php
if (!defined('ABSPATH')) {
    exit;
}

interface MessengerInterface {
    // اطلاعات پایه پیام‌رسان
    public function get_name();
    public function get_version();
    public function get_status();

    // مدیریت گروه‌ها در پیام‌رسان و دیتابیس
    public function create_group($group_data);
    public function update_group($group_id, $group_data);
    public function delete_group($group_id);
    public function get_group_stats($group_id);
    public function check_group_status($group_id);
    public function save_group_to_db($group_data);
    public function update_group_in_db($group_id, $group_data);
    public function delete_group_from_db($group_id);

    // مدیریت اعضا و ذخیره‌سازی
    public function get_group_members($group_id);
    public function add_member($group_id, $user_id);
    public function remove_member($group_id, $user_id);
    public function save_member_to_db($member_data);
    public function update_member_in_db($member_id, $member_data);

    // مدیریت پیام‌ها و لاگ‌ها
    public function send_message($chat_id, $message, $options = []);
    public function edit_message($chat_id, $message_id, $new_message);
    public function delete_message($chat_id, $message_id);
    public function log_message($message_data);
    public function get_message_history($chat_id);

    // دریافت و ذخیره آپدیت‌ها
    public function get_updates($offset = null);
    public function save_updates_to_db($updates);

    // مدیریت فایل‌ها و ذخیره‌سازی
    public function send_file($chat_id, $file_path, $caption = '');
    public function download_file($file_id);
    public function save_file_metadata($file_data);
}
