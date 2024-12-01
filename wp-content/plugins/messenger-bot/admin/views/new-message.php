<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap messenger-new-message">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <form method="post" action="" class="new-message-form">
        <?php wp_nonce_field('messenger_new_message', 'messenger_nonce'); ?>

        <div class="form-section">
            <h2>انتخاب گروه‌ها</h2>
            <div class="groups-selection">
                <?php foreach ($groups as $group): ?>
                    <label class="group-item">
                        <input type="checkbox" name="groups[]" value="<?php echo esc_attr($group->id); ?>">
                        <?php echo esc_html($group->group_title); ?>
                        (<?php echo esc_html($group->messenger_name); ?>)
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="form-section">
            <h2>محتوای پیام</h2>
            <div class="message-content">
                <div class="message-type-selector">
                    <label>
                        <input type="radio" name="message_type" value="text" checked>
                        متن
                    </label>
                    <label>
                        <input type="radio" name="message_type" value="photo">
                        تصویر
                    </label>
                    <label>
                        <input type="radio" name="message_type" value="video">
                        ویدیو
                    </label>
                    <label>
                        <input type="radio" name="message_type" value="file">
                        فایل
                    </label>
                </div>

                <div class="message-input text-input active">
                    <?php wp_editor('', 'message_text', [
                        'textarea_rows' => 10,
                        'media_buttons' => false,
                        'teeny' => true,
                    ]); ?>
                </div>

                <div class="message-input media-input">
                    <input type="file" name="media_file" id="media_file">
                    <p class="description">حداکثر حجم فایل: 50MB</p>
                </div>

                <div class="message-options">
                    <label>
                        <input type="checkbox" name="parse_mode" value="html">
                        استفاده از HTML در متن
                    </label>
                    <label>
                        <input type="checkbox" name="disable_notification">
                        ارسال بدون نوتیفیکیشن
                    </label>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h2>زمان‌بندی</h2>
            <div class="scheduling-options">
                <label>
                    <input type="radio" name="send_time" value="now" checked>
                    ارسال فوری
                </label>
                <label>
                    <input type="radio" name="send_time" value="scheduled">
                    زمان‌بندی شده
                </label>
                <div class="schedule-datetime" style="display: none;">
                    <input type="datetime-local" name="scheduled_time">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h2>تنظیمات پیشرفته</h2>
            <div class="advanced-settings">
                <label>
                    <input type="checkbox" name="retry_on_failure">
                    تلاش مجدد در صورت شکست
                </label>
                <div class="retry-options" style="display: none;">
                    <label>تعداد تلاش:
                        <input type="number" name="retry_count" value="3" min="1" max="5">
                    </label>
                    <label>فاصله بین تلاش‌ها (دقیقه):
                        <input type="number" name="retry_interval" value="5" min="1">
                    </label>
                </div>
            </div>
        </div>

        <div class="submit-section">
            <button type="submit" name="send_message" class="button button-primary">
                ارسال پیام
            </button>
            <button type="submit" name="save_draft" class="button">
                ذخیره پیش‌نویس
            </button>
        </div>
    </form>
</div>
