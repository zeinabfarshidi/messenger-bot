<?php
if (!defined('ABSPATH')) {
    exit;
}

class Messenger_Stats_Controller {
    private $db;

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
    }

    public function display() {
        // دریافت آمار کلی
        $total_messages = $this->get_total_messages();
        $successful_messages = $this->get_successful_messages();
        $failed_messages = $this->get_failed_messages();

        // دریافت آمار پیام‌رسان‌ها
        $messenger_stats = $this->get_messenger_stats();

        // دریافت گزارش عملکرد
        $performance_stats = $this->get_performance_stats();

        // نمایش صفحه
        include MESSENGER_BOT_PLUGIN_DIR . 'admin/views/stats.php';
    }

    public function get_chart_data() {
        $period = isset($_POST['period']) ? intval($_POST['period']) : 7;
        $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'messages';

        $data = [];
        $labels = [];

        for ($i = $period - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = $date;

            switch ($type) {
                case 'messages':
                    $data[] = $this->get_messages_count_for_date($date);
                    break;
                case 'groups':
                    $data[] = $this->get_groups_count_for_date($date);
                    break;
                case 'members':
                    $data[] = $this->get_members_count_for_date($date);
                    break;
            }
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function get_total_messages() {
        return $this->db->get_var(
            "SELECT COUNT(*) FROM {$this->db->prefix}messenger_messages"
        );
    }

    private function get_successful_messages() {
        return $this->db->get_var(
            "SELECT COUNT(*) FROM {$this->db->prefix}messenger_messages WHERE status = 'sent'"
        );
    }

    private function get_failed_messages() {
        return $this->db->get_var(
            "SELECT COUNT(*) FROM {$this->db->prefix}messenger_messages WHERE status = 'failed'"
        );
    }

    private function get_messenger_stats() {
        $stats = [];
        $messengers = ['telegram', 'bale', 'soroush'];

        foreach ($messengers as $messenger) {
            $count = $this->db->get_var(
                $this->db->prepare(
                    "SELECT COUNT(*) FROM {$this->db->prefix}messenger_messages 
                     WHERE messenger_type = %s",
                    $messenger
                )
            );

            $stats[] = [
                'name' => ucfirst($messenger),
                'count' => $count
            ];
        }

        return $stats;
    }

    private function get_performance_stats() {
        $stats = [];
        $messengers = ['telegram', 'bale', 'soroush'];

        foreach ($messengers as $messenger) {
            $total = $this->db->get_var(
                $this->db->prepare(
                    "SELECT COUNT(*) FROM {$this->db->prefix}messenger_messages 
                     WHERE messenger_type = %s",
                    $messenger
                )
            );

            $success = $this->db->get_var(
                $this->db->prepare(
                    "SELECT COUNT(*) FROM {$this->db->prefix}messenger_messages 
                     WHERE messenger_type = %s AND status = 'sent'",
                    $messenger
                )
            );

            $failed = $total - $success;
            $success_rate = $total > 0 ? round(($success / $total) * 100, 2) : 0;

            $avg_time = $this->db->get_var(
                $this->db->prepare(
                    "SELECT AVG(send_time) FROM {$this->db->prefix}messenger_messages 
                     WHERE messenger_type = %s AND status = 'sent'",
                    $messenger
                )
            );

            $stats[] = [
                'messenger' => ucfirst($messenger),
                'total' => $total,
                'success' => $success,
                'failed' => $failed,
                'success_rate' => $success_rate,
                'avg_time' => round($avg_time, 2)
            ];
        }

        return $stats;
    }

    private function get_messages_count_for_date($date) {
        return $this->db->get_var(
            $this->db->prepare(
                "SELECT COUNT(*) FROM {$this->db->prefix}messenger_messages 
                 WHERE DATE(sent_at) = %s",
                $date
            )
        );
    }

    private function get_groups_count_for_date($date) {
        return $this->db->get_var(
            $this->db->prepare(
                "SELECT COUNT(*) FROM {$this->db->prefix}messenger_groups 
                 WHERE DATE(created_at) <= %s",
                $date
            )
        );
    }

    private function get_members_count_for_date($date) {
        return $this->db->get_var(
            $this->db->prepare(
                "SELECT SUM(member_count) FROM {$this->db->prefix}messenger_groups 
                 WHERE DATE(created_at) <= %s",
                $date
            )
        );
    }
}
