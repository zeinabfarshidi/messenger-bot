<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap messenger-dashboard">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="dashboard-grid">
        <!-- Quick Stats -->
        <div class="dashboard-card stats-card">
            <h2>آمار کلی</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <span class="stat-value"><?php echo esc_html($total_groups); ?></span>
                    <span class="stat-label">گروه‌ها</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?php echo esc_html($total_messages); ?></span>
                    <span class="stat-label">پیام‌ها</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?php echo esc_html($total_members); ?></span>
                    <span class="stat-label">کاربران</span>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="dashboard-card activities-card">
            <h2>فعالیت‌های اخیر</h2>
            <div class="activities-list">
                <?php foreach ($recent_activities as $activity): ?>
                    <div class="activity-item">
                        <span class="activity-time"><?php echo esc_html($activity->time); ?></span>
                        <span class="activity-text"><?php echo esc_html($activity->description); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Messenger Status -->
        <div class="dashboard-card status-card">
            <h2>وضعیت پیام‌رسان‌ها</h2>
            <div class="status-list">
                <?php foreach ($messenger_status as $messenger): ?>
                    <div class="status-item <?php echo esc_attr($messenger->status); ?>">
                        <span class="messenger-name"><?php echo esc_html($messenger->name); ?></span>
                        <span class="status-indicator"></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-card actions-card">
            <h2>دسترسی سریع</h2>
            <div class="quick-actions">
                <a href="<?php echo esc_url(admin_url('admin.php?page=messenger-groups')); ?>" class="action-button">
                    <span class="dashicons dashicons-groups"></span>
                    مدیریت گروه‌ها
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=messenger-messages')); ?>" class="action-button">
                    <span class="dashicons dashicons-email"></span>
                    مدیریت پیام‌ها
                </a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=messenger-settings')); ?>" class="action-button">
                    <span class="dashicons dashicons-admin-settings"></span>
                    تنظیمات
                </a>
            </div>
        </div>
    </div>
</div>
