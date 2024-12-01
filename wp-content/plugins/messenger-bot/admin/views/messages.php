<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap messenger-messages">
    <h1 class="wp-heading-inline">پیام‌ها</h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=messenger-messages&action=new')); ?>" class="page-title-action">
        ایجاد پیام جدید
    </a>

    <form method="get">
        <input type="hidden" name="page" value="messenger-messages">

        <div class="tablenav top">
            <div class="alignleft actions">
                <select name="status">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="draft" <?php selected(isset($_GET['status']) && $_GET['status'] === 'draft'); ?>>پیش‌نویس</option>
                    <option value="scheduled" <?php selected(isset($_GET['status']) && $_GET['status'] === 'scheduled'); ?>>زمان‌بندی شده</option>
                    <option value="sent" <?php selected(isset($_GET['status']) && $_GET['status'] === 'sent'); ?>>ارسال شده</option>
                    <option value="failed" <?php selected(isset($_GET['status']) && $_GET['status'] === 'failed'); ?>>ناموفق</option>
                </select>

                <select name="group_id">
                    <option value="">همه گروه‌ها</option>
                    <?php foreach ($groups as $group): ?>
                        <option value="<?php echo esc_attr($group->id); ?>" <?php selected(isset($_GET['group_id']) && $_GET['group_id'] == $group->id); ?>>
                            <?php echo esc_html($group->group_title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="date_range">
                    <option value="">همه زمان‌ها</option>
                    <option value="today" <?php selected(isset($_GET['date_range']) && $_GET['date_range'] === 'today'); ?>>امروز</option>
                    <option value="yesterday" <?php selected(isset($_GET['date_range']) && $_GET['date_range'] === 'yesterday'); ?>>دیروز</option>
                    <option value="week" <?php selected(isset($_GET['date_range']) && $_GET['date_range'] === 'week'); ?>>هفته گذشته</option>
                    <option value="month" <?php selected(isset($_GET['date_range']) && $_GET['date_range'] === 'month'); ?>>ماه گذشته</option>
                </select>

                <?php submit_button('فیلتر', 'action', 'filter', false); ?>
            </div>

            <div class="tablenav-pages">
                <?php echo $pagination; ?>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
            <tr>
                <th scope="col" class="manage-column column-title">عنوان پیام</th>
                <th scope="col" class="manage-column column-groups">گروه‌های هدف</th>
                <th scope="col" class="manage-column column-status">وضعیت</th>
                <th scope="col" class="manage-column column-schedule">زمان ارسال</th>
                <th scope="col" class="manage-column column-sent">تعداد ارسال</th>
                <th scope="col" class="manage-column column-actions">عملیات</th>
            </tr>
            </thead>

            <tbody>
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $message): ?>
                    <tr>
                        <td class="column-title">
                            <strong>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=messenger-messages&action=edit&id=' . $message->id)); ?>">
                                    <?php echo esc_html($message->title); ?>
                                </a>
                            </strong>
                            <div class="row-actions">
                                    <span class="edit">
                                        <a href="<?php echo esc_url(admin_url('admin.php?page=messenger-messages&action=edit&id=' . $message->id)); ?>">ویرایش</a> |
                                    </span>
                                <span class="duplicate">
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=messenger-messages&action=duplicate&id=' . $message->id), 'duplicate_message')); ?>">تکرار</a> |
                                    </span>
                                <span class="delete">
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=messenger-messages&action=delete&id=' . $message->id), 'delete_message')); ?>"
                                           onclick="return confirm('آیا از حذف این پیام اطمینان دارید؟');">حذف</a>
                                    </span>
                            </div>
                        </td>
                        <td class="column-groups">
                            <?php
                            if (!empty($message->target_groups)) {
                                $group_names = array_map(function($group_id) use ($groups) {
                                    foreach ($groups as $group) {
                                        if ($group->id == $group_id) {
                                            return $group->group_title;
                                        }
                                    }
                                    return '';
                                }, $message->target_groups);
                                echo esc_html(implode(', ', array_filter($group_names)));
                            }
                            ?>
                        </td>
                        <td class="column-status">
                                <span class="status-<?php echo esc_attr($message->status); ?>">
                                    <?php
                                    switch ($message->status) {
                                        case 'draft':
                                            echo 'پیش‌نویس';
                                            break;
                                        case 'scheduled':
                                            echo 'زمان‌بندی شده';
                                            break;
                                        case 'sent':
                                            echo 'ارسال شده';
                                            break;
                                        case 'failed':
                                            echo 'ناموفق';
                                            break;
                                    }
                                    ?>
                                </span>
                        </td>
                        <td class="column-schedule">
                            <?php
                            if ($message->schedule_time) {
                                echo esc_html(date_i18n('Y/m/d H:i', strtotime($message->schedule_time)));
                            } else {
                                echo 'فوری';
                            }
                            ?>
                        </td>
                        <td class="column-sent">
                            <?php echo esc_html($message->sent_count); ?> از <?php echo esc_html($message->total_count); ?>
                        </td>
                        <td class="column-actions">
                            <?php if ($message->status === 'draft' || $message->status === 'failed'): ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=messenger-messages&action=send&id=' . $message->id), 'send_message')); ?>"
                                   class="button button-small">
                                    ارسال
                                </a>
                            <?php endif; ?>

                            <?php if ($message->status === 'scheduled'): ?>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=messenger-messages&action=cancel&id=' . $message->id), 'cancel_message')); ?>"
                                   class="button button-small">
                                    لغو ارسال
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="no-items">هیچ پیامی یافت نشد.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </form>
</div>
