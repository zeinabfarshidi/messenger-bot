<?php
if (!defined('ABSPATH')) {
    exit;
}

class Messenger_Messages_Controller {
    private $per_page = 20;
    private $db;

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function display() {
        // دریافت شماره صفحه جاری
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;

        // محاسبه offset برای کوئری
        $offset = ($current_page - 1) * $this->per_page;

        // دریافت فیلترها
        $group_filter = isset($_GET['group']) ? intval($_GET['group']) : 0;
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

        // ساخت کوئری
        $query = "SELECT m.*, g.group_title, g.messenger_name 
                 FROM {$this->db->prefix}messenger_messages m
                 LEFT JOIN {$this->db->prefix}messenger_groups g ON m.group_id = g.id
                 WHERE 1=1";

        $count_query = "SELECT COUNT(*) 
                       FROM {$this->db->prefix}messenger_messages m
                       LEFT JOIN {$this->db->prefix}messenger_groups g ON m.group_id = g.id
                       WHERE 1=1";

        $where = [];
        $where_count = [];

        if ($group_filter) {
            $where[] = $this->db->prepare("AND m.group_id = %d", $group_filter);
            $where_count[] = $this->db->prepare("AND m.group_id = %d", $group_filter);
        }

        if ($status_filter) {
            $where[] = $this->db->prepare("AND m.status = %s", $status_filter);
            $where_count[] = $this->db->prepare("AND m.status = %s", $status_filter);
        }

        if ($search) {
            $where[] = $this->db->prepare("AND m.message_text LIKE '%%%s%%'", $search);
            $where_count[] = $this->db->prepare("AND m.message_text LIKE '%%%s%%'", $search);
        }

        // اضافه کردن شرط‌ها به کوئری
        if (!empty($where)) {
            $query .= ' ' . implode(' ', $where);
            $count_query .= ' ' . implode(' ', $where_count);
        }

        // اضافه کردن ترتیب و محدودیت
        $query .= " ORDER BY m.sent_at DESC LIMIT {$offset}, {$this->per_page}";

        // دریافت داده‌ها
        $messages = $this->db->get_results($query);
        $total_items = $this->db->get_var($count_query);
        $total_pages = ceil($total_items / $this->per_page);

        // دریافت لیست گروه‌ها
        $groups = $this->get_groups_list();

        // ساخت پیجینیشن
        $pagination = paginate_links([
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'prev_text' => __('&laquo;'),
            'next_text' => __('&raquo;'),
            'total' => $total_pages,
            'current' => $current_page
        ]);

        // نمایش view
        include MESSENGER_BOT_PLUGIN_DIR . 'admin/views/messages.php';
    }

    private function get_groups_list() {
        return $this->db->get_results(
            "SELECT id, group_title FROM {$this->db->prefix}messenger_groups ORDER BY group_title ASC"
        );
    }

    public function handle_actions() {
        if (!isset($_GET['action'])) {
            return;
        }

        $action = $_GET['action'];
        $message_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        switch ($action) {
            case 'delete':
                $this->delete_message($message_id);
                break;
            case 'new':
                $this->new_message();
                break;
            case 'resend':
                $this->resend_message($message_id);
                break;
        }
    }

    private function delete_message($message_id) {
        if (!current_user_can('manage_options')) {
            wp_die(__('شما اجازه دسترسی به این بخش را ندارید.'));
        }

        $this->db->delete(
            $this->db->prefix . 'messenger_messages',
            ['id' => $message_id],
            ['%d']
        );

        wp_redirect(admin_url('admin.php?page=messenger-messages&deleted=1'));
        exit;
    }

    private function new_message() {
        // کد مربوط به ارسال پیام جدید
    }

    private function resend_message($message_id) {
        // کد مربوط به ارسال مجدد پیام
    }

    public function get_message_details($message_id) {
        return $this->db->get_row(
            $this->db->prepare(
                "SELECT m.*, g.group_title, g.messenger_name 
                 FROM {$this->db->prefix}messenger_messages m
                 LEFT JOIN {$this->db->prefix}messenger_groups g ON m.group_id = g.id
                 WHERE m.id = %d",
                $message_id
            )
        );
    }
}
