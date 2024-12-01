<?php
if (!defined('ABSPATH')) {
    exit;
}

class Messenger_Groups_Controller {
    private $per_page = 10;
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
        $messenger_filter = isset($_GET['messenger']) ? sanitize_text_field($_GET['messenger']) : '';
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

        // ساخت کوئری
        $query = "SELECT * FROM {$this->db->prefix}messenger_groups WHERE 1=1";
        $count_query = "SELECT COUNT(*) FROM {$this->db->prefix}messenger_groups WHERE 1=1";

        $where = [];
        $where_count = [];

        if ($messenger_filter) {
            $where[] = $this->db->prepare("AND messenger_name = %s", $messenger_filter);
            $where_count[] = $this->db->prepare("AND messenger_name = %s", $messenger_filter);
        }

        if ($search) {
            $where[] = $this->db->prepare("AND (group_title LIKE '%%%s%%' OR group_id LIKE '%%%s%%')",
                $search, $search);
            $where_count[] = $this->db->prepare("AND (group_title LIKE '%%%s%%' OR group_id LIKE '%%%s%%')",
                $search, $search);
        }

        // اضافه کردن شرط‌ها به کوئری
        if (!empty($where)) {
            $query .= ' ' . implode(' ', $where);
            $count_query .= ' ' . implode(' ', $where_count);
        }

        // اضافه کردن ترتیب و محدودیت
        $query .= " ORDER BY created_at DESC LIMIT {$offset}, {$this->per_page}";

        // دریافت داده‌ها
        $groups = $this->db->get_results($query);
        $total_items = $this->db->get_var($count_query);
        $total_pages = ceil($total_items / $this->per_page);

        // ساخت لینک‌های پیجینیشن
        $pagination = paginate_links([
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'prev_text' => __('&laquo;'),
            'next_text' => __('&raquo;'),
            'total' => $total_pages,
            'current' => $current_page
        ]);

        // دریافت لیست پیام‌رسان‌ها
        $messengers = $this->get_messengers_list();

        // نمایش view
        include MESSENGER_BOT_PLUGIN_DIR . 'admin/views/groups.php';
    }

    private function get_messengers_list() {
        return [
            (object)[
                'name' => 'telegram',
                'title' => 'تلگرام'
            ],
            (object)[
                'name' => 'bale',
                'title' => 'بله'
            ],
            (object)[
                'name' => 'soroush',
                'title' => 'سروش'
            ],
            (object)[
                'name' => 'eitaa',
                'title' => 'ایتا'
            ]
        ];
    }

    public function handle_actions() {
        if (!isset($_GET['action'])) {
            return;
        }

        $action = $_GET['action'];
        $group_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        switch ($action) {
            case 'delete':
                $this->delete_group($group_id);
                break;
            case 'edit':
                $this->edit_group($group_id);
                break;
            case 'new':
                $this->new_group();
                break;
        }
    }

    private function delete_group($group_id) {
        if (!current_user_can('manage_options')) {
            wp_die(__('شما اجازه دسترسی به این بخش را ندارید.'));
        }

        $this->db->delete(
            $this->db->prefix . 'messenger_groups',
            ['id' => $group_id],
            ['%d']
        );

        wp_redirect(admin_url('admin.php?page=messenger-groups&deleted=1'));
        exit;
    }

    private function edit_group($group_id) {
        // کد مربوط به ویرایش گروه
    }

    private function new_group() {
        // کد مربوط به ایجاد گروه جدید
    }
}
