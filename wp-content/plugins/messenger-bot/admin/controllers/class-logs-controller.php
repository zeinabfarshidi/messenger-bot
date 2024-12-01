<?php
if (!defined('ABSPATH')) {
    exit;
}

class Messenger_Logs_Controller {
    private $db;
    private $per_page = 20;

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function display() {
        // بررسی درخواست پاک کردن لاگ‌ها
        if (isset($_POST['clear_logs']) && current_user_can('manage_options')) {
            $this->clear_logs();
            wp_redirect(add_query_arg('logs_cleared', '1'));
            exit;
        }

        // دریافت پارامترهای فیلتر
        $filters = $this->get_filters();

        // دریافت لاگ‌ها
        $logs = $this->get_logs($filters);

        // ساخت پیجینیشن
        $pagination = $this->build_pagination($filters);

        // نمایش صفحه
        include MESSENGER_BOT_PLUGIN_DIR . 'admin/views/logs.php';
    }

    private function get_filters() {
        return [
            'log_level' => isset($_GET['log_level']) ? sanitize_text_field($_GET['log_level']) : '',
            'messenger_type' => isset($_GET['messenger_type']) ? sanitize_text_field($_GET['messenger_type']) : '',
            'date_from' => isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '',
            'date_to' => isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '',
            'search' => isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '',
            'paged' => isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1
        ];
    }

    private function get_logs($filters) {
        $query = "SELECT * FROM {$this->db->prefix}messenger_logs WHERE 1=1";
        $where = [];
        $args = [];

        if (!empty($filters['log_level'])) {
            $where[] = "level = %s";
            $args[] = $filters['log_level'];
        }

        if (!empty($filters['messenger_type'])) {
            $where[] = "messenger = %s";
            $args[] = $filters['messenger_type'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = "time >= %s";
            $args[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where[] = "time <= %s";
            $args[] = $filters['date_to'] . ' 23:59:59';
        }

        if (!empty($filters['search'])) {
            $where[] = "(message LIKE %s OR details LIKE %s)";
            $args[] = '%' . $filters['search'] . '%';
            $args[] = '%' . $filters['search'] . '%';
        }

        if (!empty($where)) {
            $query .= " AND " . implode(" AND ", $where);
        }

        $query .= " ORDER BY time DESC";

        // اضافه کردن محدودیت
        $offset = ($filters['paged'] - 1) * $this->per_page;
        $query .= " LIMIT {$offset}, {$this->per_page}";

        if (!empty($args)) {
            $query = $this->db->prepare($query, $args);
        }

        return $this->db->get_results($query);
    }

    private function build_pagination($filters) {
        // محاسبه تعداد کل آیتم‌ها
        $total_items = $this->count_logs($filters);
        $total_pages = ceil($total_items / $this->per_page);

        return paginate_links([
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'prev_text' => __('&laquo;'),
            'next_text' => __('&raquo;'),
            'total' => $total_pages,
            'current' => $filters['paged']
        ]);
    }

    private function count_logs($filters) {
        $query = "SELECT COUNT(*) FROM {$this->db->prefix}messenger_logs WHERE 1=1";
        $where = [];
        $args = [];

        if (!empty($filters['log_level'])) {
            $where[] = "level = %s";
            $args[] = $filters['log_level'];
        }

        if (!empty($filters['messenger_type'])) {
            $where[] = "messenger = %s";
            $args[] = $filters['messenger_type'];
        }

        if (!empty($filters['date_from'])) {
            $where[] = "time >= %s";
            $args[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $where[] = "time <= %s";
            $args[] = $filters['date_to'] . ' 23:59:59';
        }

        if (!empty($filters['search'])) {
            $where[] = "(message LIKE %s OR details LIKE %s)";
            $args[] = '%' . $filters['search'] . '%';
            $args[] = '%' . $filters['search'] . '%';
        }

        if (!empty($where)) {
            $query .= " AND " . implode(" AND ", $where);
        }

        if (!empty($args)) {
            $query = $this->db->prepare($query, $args);
        }

        return $this->db->get_var($query);
    }

    private function clear_logs() {
        $this->db->query("TRUNCATE TABLE {$this->db->prefix}messenger_logs");
    }

    public function get_log_details($log_id) {
        return $this->db->get_row(
            $this->db->prepare(
                "SELECT * FROM {$this->db->prefix}messenger_logs WHERE id = %d",
                $log_id
            )
        );
    }
}
