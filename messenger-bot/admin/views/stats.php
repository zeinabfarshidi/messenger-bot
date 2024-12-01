<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap messenger-stats">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <!-- نمودار آماری -->
    <div class="stats-chart-container">
        <div class="chart-filters">
            <select id="chart-period">
                <option value="7">7 روز گذشته</option>
                <option value="30">30 روز گذشته</option>
                <option value="90">3 ماه گذشته</option>
            </select>
            <select id="chart-type">
                <option value="messages">تعداد پیام‌ها</option>
                <option value="groups">تعداد گروه‌ها</option>
                <option value="members">تعداد اعضا</option>
            </select>
        </div>
        <canvas id="stats-chart"></canvas>
    </div>

    <!-- آمار کلی -->
    <div class="stats-overview">
        <div class="stats-card">
            <h3>آمار کلی پیام‌ها</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-label">کل پیام‌ها</span>
                    <span class="stat-value"><?php echo esc_html($total_messages); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">پیام‌های موفق</span>
                    <span class="stat-value"><?php echo esc_html($successful_messages); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">پیام‌های ناموفق</span>
                    <span class="stat-value"><?php echo esc_html($failed_messages); ?></span>
                </div>
            </div>
        </div>

        <div class="stats-card">
            <h3>آمار پیام‌رسان‌ها</h3>
            <div class="stats-grid">
                <?php foreach ($messenger_stats as $messenger): ?>
                    <div class="stat-item">
                        <span class="stat-label"><?php echo esc_html($messenger['name']); ?></span>
                        <span class="stat-value"><?php echo esc_html($messenger['count']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- گزارش عملکرد -->
    <div class="performance-report">
        <h2>گزارش عملکرد</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
            <tr>
                <th>پیام‌رسان</th>
                <th>تعداد پیام</th>
                <th>موفق</th>
                <th>ناموفق</th>
                <th>نرخ موفقیت</th>
                <th>میانگین زمان ارسال</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($performance_stats as $stat): ?>
                <tr>
                    <td><?php echo esc_html($stat['messenger']); ?></td>
                    <td><?php echo esc_html($stat['total']); ?></td>
                    <td><?php echo esc_html($stat['success']); ?></td>
                    <td><?php echo esc_html($stat['failed']); ?></td>
                    <td><?php echo esc_html($stat['success_rate']); ?>%</td>
                    <td><?php echo esc_html($stat['avg_time']); ?> ثانیه</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- نمودار دایره‌ای توزیع پیام‌ها -->
    <div class="stats-pie-charts">
        <div class="pie-chart-container">
            <h3>توزیع پیام‌ها بر اساس پیام‌رسان</h3>
            <canvas id="messenger-distribution-chart"></canvas>
        </div>
        <div class="pie-chart-container">
            <h3>وضعیت پیام‌ها</h3>
            <canvas id="message-status-chart"></canvas>
        </div>
    </div>
</div>

<!-- کدهای جاوااسکریپت برای نمودارها -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // کد مربوط به رسم نمودارها با Chart.js
        const ctx = document.getElementById('stats-chart').getContext('2d');
        const distributionCtx = document.getElementById('messenger-distribution-chart').getContext('2d');
        const statusCtx = document.getElementById('message-status-chart').getContext('2d');

        // اینجا کدهای مربوط به رسم نمودارها را می‌نویسیم
    });
</script>
