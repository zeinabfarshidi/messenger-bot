<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap messenger-logs">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <!-- فیلترهای لاگ -->
    <div class="log-filters">
        <form method="get">
            <input type="hidden" name="page" value="messenger-logs">

            <select name="log_level">
                <option value="">همه سطوح</option>
                <option value="info" <?php selected(isset($_GET['log_level']) && $_GET['log_level'] === 'info'); ?>>اطلاعات</option>
                <option value="warning" <?php selected(isset($_GET['log_level']) && $_GET['log_level'] === 'warning'); ?>>هشدار</option>
                <option value="error" <?php selected(isset($_GET['log_level']) && $_GET['log_level'] === 'error'); ?>>خطا</option>
            </select>

            <select name="messenger_type">
                <option value="">همه پیام‌رسان‌ها</option>
                <option value="telegram" <?php selected(isset($_GET['messenger_type']) && $_GET['messenger_type'] === 'telegram'); ?>>تلگرام</option>
                <option value="bale" <?php selected(isset($_GET['messenger_type']) && $_GET['messenger_type'] === 'bale'); ?>>بله</option>
                <option value="soroush" <?php selected(isset($_GET['messenger_type']) && $_GET['messenger_type'] === 'soroush'); ?>>سروش</option>
            </select>

            <input type="date" name="date_from" value="<?php echo isset($_GET['date_from']) ? esc_attr($_GET['date_from']) : ''; ?>" placeholder="از تاریخ">
            <input type="date" name="date_to" value="<?php echo isset($_GET['date_to']) ? esc_attr($_GET['date_to']) : ''; ?>" placeholder="تا تاریخ">

            <input type="text" name="search" value="<?php echo isset($_GET['search']) ? esc_attr($_GET['search']) : ''; ?>" placeholder="جستجو در لاگ‌ها...">

            <?php submit_button('اعمال فیلتر', 'secondary', 'filter_logs', false); ?>
            <?php submit_button('پاک کردن لاگ‌ها', 'delete', 'clear_logs', false); ?>
        </form>
    </div>

    <!-- جدول لاگ‌ها -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
        <tr>
            <th scope="col" class="manage-column column-time">زمان</th>
            <th scope="col" class="manage-column column-level">سطح</th>
            <th scope="col" class="manage-column column-messenger">پیام‌رسان</th>
            <th scope="col" class="manage-column column-message">پیام</th>
            <th scope="col" class="manage-column column-details">جزئیات</th>
        </tr>
        </thead>
        <tbody>
        <?php if (!empty($logs)): ?>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?php echo esc_html($log->time); ?></td>
                    <td>
                            <span class="log-level log-level-<?php echo esc_attr($log->level); ?>">
                                <?php echo esc_html($log->level); ?>
                            </span>
                    </td>
                    <td><?php echo esc_html($log->messenger); ?></td>
                    <td><?php echo esc_html($log->message); ?></td>
                    <td>
                        <button class="button button-small view-log-details"
                                data-log-id="<?php echo esc_attr($log->id); ?>">
                            مشاهده جزئیات
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="no-logs">هیچ لاگی یافت نشد.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

    <!-- پیجینیشن -->
    <div class="tablenav bottom">
        <?php echo $pagination; ?>
    </div>

    <!-- مودال جزئیات لاگ -->
    <div id="log-details-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>جزئیات لاگ</h2>
            <div class="log-details-content">
                <pre></pre>
            </div>
        </div>
    </div>
</div>
