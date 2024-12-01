<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap messenger-groups">
    <h1 class="wp-heading-inline">گروه‌ها</h1>
    <a href="<?php echo esc_url(admin_url('admin.php?page=messenger-groups&action=new')); ?>" class="page-title-action">
        افزودن گروه جدید
    </a>

    <form method="get">
        <input type="hidden" name="page" value="messenger-groups">

        <div class="tablenav top">
            <div class="alignleft actions">
                <select name="messenger_type">
                    <option value="">همه پیام‌رسان‌ها</option>
                    <option value="telegram" <?php selected(isset($_GET['messenger_type']) && $_GET['messenger_type'] === 'telegram'); ?>>تلگرام</option>
                    <option value="bale" <?php selected(isset($_GET['messenger_type']) && $_GET['messenger_type'] === 'bale'); ?>>بله</option>
                    <option value="soroush" <?php selected(isset($_GET['messenger_type']) && $_GET['messenger_type'] === 'soroush'); ?>>سروش</option>
                </select>

                <select name="status">
                    <option value="">همه وضعیت‌ها</option>
                    <option value="active" <?php selected(isset($_GET['status']) && $_GET['status'] === 'active'); ?>>فعال</option>
                    <option value="inactive" <?php selected(isset($_GET['status']) && $_GET['status'] === 'inactive'); ?>>غیرفعال</option>
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
                <th scope="col" class="manage-column column-title">عنوان گروه</th>
                <th scope="col" class="manage-column column-messenger">پیام‌رسان</th>
                <th scope="col" class="manage-column column-identifier">شناسه</th>
                <th scope="col" class="manage-column column-members">تعداد اعضا</th>
                <th scope="col" class="manage-column column-status">وضعیت</th>
                <th scope="col" class="manage-column column-last-message">آخرین پیام</th>
                <th scope="col" class="manage-column column-actions">عملیات</th>
            </tr>
            </thead>

            <tbody>
            <?php if (!empty($groups)): ?>
                <?php foreach ($groups as $group): ?>
                    <tr>
                        <td class="column-title">
                            <strong>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=messenger-groups&action=edit&id=' . $group->id)); ?>">
                                    <?php echo esc_html($group->group_title); ?>
                                </a>
                            </strong>
                        </td>
                        <td class="column-messenger">
                            <?php echo esc_html(ucfirst($group->messenger_name)); ?>
                        </td>
                        <td class="column-identifier">
                            <?php echo esc_html($group->group_identifier); ?>
                        </td>
                        <td class="column-members">
                            <?php echo esc_html($group->member_count); ?>
                        </td>
                        <td class="column-status">
                                <span class="status-<?php echo esc_attr($group->status); ?>">
                                    <?php echo $group->status === 'active' ? 'فعال' : 'غیرفعال'; ?>
                                </span>
                        </td>
                        <td class="column-last-message">
                            <?php
                            if ($group->last_message_time) {
                                echo esc_html(human_time_diff(strtotime($group->last_message_time), current_time('timestamp'))) . ' پیش';
                            } else {
                                echo 'بدون پیام';
                            }
                            ?>
                        </td>
                        <td class="column-actions">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=messenger-groups&action=edit&id=' . $group->id)); ?>"
                               class="button button-small">
                                ویرایش
                            </a>
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=messenger-groups&action=delete&id=' . $group->id), 'delete_group')); ?>"
                               class="button button-small button-link-delete"
                               onclick="return confirm('آیا از حذف این گروه اطمینان دارید؟');">
                                حذف
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="no-items">هیچ گروهی یافت نشد.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </form>
</div>
