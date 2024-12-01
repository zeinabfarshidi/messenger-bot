<?php
if (!defined('ABSPATH')) {
    exit;
}

$message_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = $message_id ? $controller->get_message($message_id) : null;
?>

<div class="wrap messenger-edit-message">
    <h1><?php echo $message_id ? 'ویرایش پیام' : 'پیام جدید'; ?></h1>

    <form method="post" action="" class="edit-message-form" enctype="multipart/form-data">
        <?php wp_nonce_field('messenger_edit_message', 'messenger_nonce'); ?>
        <input type="hidden" name="message_id" value="<?php echo esc_attr($message_id); ?>">

        <div class="form-section">
            <h2>اطلاعات پیام</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="message_title">عنوان پیام</label>
                    </th>
                    <td>
                        <input type="text" id="message_title" name="message_title" class="regular-text"
                               value="<?php echo $message ? esc_attr($message->title) : ''; ?>">
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="message_content">متن پیام</label>
                    </th>
                    <td>
                        <?php
                        wp_editor(
                            $message ? $message->content : '',
                            'message_content',
                            [
                                'textarea_rows' => 10,
                                'media_buttons' => true,
                                'teeny' => true,
                            ]
                        );
                        ?>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label>فایل ضمیمه</label>
                    </th>
                    <td>
                        <?php if ($message && $message->attachment_url): ?>
                            <div class="current-attachment">
                                <p>فایل فعلی: <?php echo esc_html(basename($message->attachment_url)); ?></p>
                                <label>
                                    <input type="checkbox" name="remove_attachment" value="1">
                                    حذف فایل
                                </label>
                            </div>
                        <?php endif; ?>
                        <input type="file" name="attachment" id="attachment">
                        <p class="description">حداکثر حجم فایل: 50MB</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="schedule_time">زمان ارسال</label>
                    </th>
                    <td>
                        <input type="datetime-local" id="schedule_time" name="schedule_time"
                               value="<?php echo $message ? esc_attr(date('Y-m-d\TH:i', strtotime($message->schedule_time))) : ''; ?>">
                        <p class="description">خالی بگذارید تا بلافاصله ارسال شود</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">گروه‌های هدف</th>
                    <td>
                        <div class="target-groups">
                            <?php foreach ($groups as $group): ?>
                                <label>
                                    <input type="checkbox" name="target_groups[]"
                                           value="<?php echo esc_attr($group->id); ?>"
                                        <?php checked($message && in_array($group->id, $message->target_groups)); ?>>
                                    <?php echo esc_html($group->group_title); ?>
                                    (<?php echo esc_html($group->messenger_name); ?>)
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th scope="row">تنظیمات ارسال</th>
                    <td>
                        <label>
                            <input type="checkbox" name="silent_mode" value="1"
                                <?php checked($message && $message->silent_mode); ?>>
                            ارسال بی‌صدا
                        </label>
                        <br>
                        <label>
                            <input type="checkbox" name="parse_mode_html" value="1"
                                <?php checked($message && $message->parse_mode === 'html'); ?>>
                            پشتیبانی از HTML
                        </label>
                    </td>
                </tr>
            </table>
        </div>

        <p class="submit">
            <button type="submit" name="save_message" class="button button-primary">
                <?php echo $message_id ? 'بروزرسانی پیام' : 'ذخیره پیام'; ?>
            </button>
            <?php if ($message_id): ?>
                <button type="submit" name="send_now" class="button">
                    ارسال فوری
                </button>
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=messenger-messages&action=delete&id=' . $message_id), 'delete_message')); ?>"
                   class="button button-link-delete"
                   onclick="return confirm('آیا از حذف این پیام اطمینان دارید؟');">
                    حذف پیام
                </a>
            <?php endif; ?>
        </p>
    </form>
</div>
