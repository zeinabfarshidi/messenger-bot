<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $messenger_manager = MessengerManager::get_instance();
} catch (Throwable $e) {
    echo 'خطا: ' . $e->getMessage() . '<br>';
    echo 'فایل: ' . $e->getFile() . '<br>';
    echo 'خط: ' . $e->getLine() . '<br>';
}



error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!defined('ABSPATH')) {
    exit;
}

try {
    $messenger_manager = MessengerManager::get_instance();
    $groups = $messenger_manager->get_all_groups();

    // پردازش افزودن گروه جدید
    if (isset($_POST['add_group']) && wp_verify_nonce($_POST['telegram_group_nonce'], 'add_telegram_group')) {
        $group_data = array(
            'messenger_name' => 'telegram',
            'group_id' => sanitize_text_field($_POST['group_id']),
            'group_title' => sanitize_text_field($_POST['group_title']),
            'group_type' => sanitize_text_field($_POST['group_type']),
            'member_count' => 0,
            'status' => 'active'
        );

        $result = $messenger_manager->save_group_to_db($group_data);

        if ($result) {
            echo '<div class="notice notice-success"><p>گروه با موفقیت اضافه شد.</p></div>';
            // بروزرسانی لیست گروه‌ها
            $groups = $messenger_manager->get_all_groups();
        } else {
            echo '<div class="notice notice-error"><p>خطا در افزودن گروه.</p></div>';
        }
    }
    // پردازش حذف گروه
    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['group_id'])) {
        $group_id = intval($_GET['group_id']);
        if ($messenger_manager->delete_group($group_id)) {
            echo '<div class="notice notice-success"><p>گروه با موفقیت حذف شد.</p></div>';
            $groups = $messenger_manager->get_all_groups(); // بروزرسانی لیست
        }
    }
} catch (Exception $e) {
    echo "خطا: " . $e->getMessage();
}
?>

<div class="groups-wrapper">
    <div class="add-new-group">
        <h2>افزودن گروه جدید</h2>
        <form method="post" action="">
            <table class="form-table">
                <tr>
                    <th><label for="group_title">نام گروه</label></th>
                    <td><input type="text" id="group_title" name="group_title" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="group_id">شناسه گروه تلگرام</label></th>
                    <td><input type="text" id="group_id" name="group_id" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="group_type">نوع گروه</label></th>
                    <td>
                        <select id="group_type" name="group_type">
                            <option value="public">عمومی</option>
                            <option value="private">خصوصی</option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php wp_nonce_field('add_telegram_group', 'telegram_group_nonce'); ?>
            <input type="submit" name="add_group" class="button button-primary" value="افزودن گروه">
        </form>
    </div>

    <div class="groups-list">
        <h2>لیست گروه‌ها</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
            <tr>
                <th>نام گروه</th>
                <th>شناسه گروه</th>
                <th>نوع</th>
                <th>تعداد اعضا</th>
                <th>وضعیت</th>
                <th>عملیات</th>
            </tr>
            </thead>
            <tbody>
            <?php if ($groups): ?>
                <?php foreach ($groups as $group): ?>
                    <tr>
                        <td><?php echo esc_html($group->group_title); ?></td>
                        <td><?php echo esc_html($group->group_id); ?></td>
                        <td><?php echo esc_html($group->group_type); ?></td>
                        <td><?php echo esc_html($group->member_count); ?></td>
                        <td><?php echo esc_html($group->status); ?></td>
                        <td>
                            <a href="?page=messenger-manager&action=edit&group_id=<?php echo $group->id; ?>" class="button">ویرایش</a>
                            <a href="?page=messenger-manager&action=delete&group_id=<?php echo $group->id; ?>" class="button" onclick="return confirm('آیا مطمئن هستید؟')">حذف</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">هیچ گروهی یافت نشد.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
