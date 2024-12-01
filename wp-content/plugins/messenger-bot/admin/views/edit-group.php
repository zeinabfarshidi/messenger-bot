<?php
if (!defined('ABSPATH')) {
    exit;
}

$group_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$group = $group_id ? $controller->get_group($group_id) : null;
?>

<div class="wrap messenger-edit-group">
    <h1><?php echo $group_id ? 'ویرایش گروه' : 'گروه جدید'; ?></h1>

    <form method="post" action="" class="edit-group-form">
        <?php wp_nonce_field('messenger_edit_group', 'messenger_nonce'); ?>
        <input type="hidden" name="group_id" value="<?php echo esc_attr($group_id); ?>">

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="group_title">عنوان گروه</label>
                </th>
                <td>
                    <input type="text" id="group_title" name="group_title" class="regular-text"
                           value="<?php echo $group ? esc_attr($group->group_title) : ''; ?>" required>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="messenger_type">نوع پیام‌رسان</label>
                </th>
                <td>
                    <select id="messenger_type" name="messenger_type" required>
                        <option value="">انتخاب کنید</option>
                        <option value="telegram" <?php selected($group && $group->messenger_name === 'telegram'); ?>>تلگرام</option>
                        <option value="bale" <?php selected($group && $group->messenger_name === 'bale'); ?>>بله</option>
                        <option value="soroush" <?php selected($group && $group->messenger_name === 'soroush'); ?>>سروش</option>
                    </select>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="group_identifier">شناسه گروه</label>
                </th>
                <td>
                    <input type="text" id="group_identifier" name="group_identifier" class="regular-text"
                           value="<?php echo $group ? esc_attr($group->group_identifier) : ''; ?>" required>
                    <p class="description">شناسه یکتای گروه در پیام‌رسان</p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="group_description">توضیحات</label>
                </th>
                <td>
                    <textarea id="group_description" name="group_description" rows="5" class="large-text">
                        <?php echo $group ? esc_textarea($group->description) : ''; ?>
                    </textarea>
                </td>
            </tr>

            <tr>
                <th scope="row">وضعیت</th>
                <td>
                    <label>
                        <input type="radio" name="status" value="active"
                            <?php checked(!$group || $group->status === 'active'); ?>>
                        فعال
                    </label>
                    <br>
                    <label>
                        <input type="radio" name="status" value="inactive"
                            <?php checked($group && $group->status === 'inactive'); ?>>
                        غیرفعال
                    </label>
                </td>
            </tr>

            <tr>
                <th scope="row">تنظیمات پیشرفته</th>
                <td>
                    <label>
                        <input type="checkbox" name="auto_retry" value="1"
                            <?php checked($group && $group->auto_retry); ?>>
                        تلاش مجدد خودکار در صورت خطا
                    </label>
                    <br>
                    <label>
                        <input type="checkbox" name="silent_mode" value="1"
                            <?php checked($group && $group->silent_mode); ?>>
                        حالت بی‌صدا
                    </label>
                </td>
            </tr>
        </table>

        <p class="submit">
            <button type="submit" name="save_group" class="button button-primary">
                <?php echo $group_id ? 'بروزرسانی گروه' : 'ایجاد گروه'; ?>
            </button>
            <?php if ($group_id): ?>
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=messenger-groups&action=delete&id=' . $group_id), 'delete_group')); ?>"
                   class="button button-link-delete"
                   onclick="return confirm('آیا از حذف این گروه اطمینان دارید؟');">
                    حذف گروه
                </a>
            <?php endif; ?>
        </p>
    </form>
</div>
