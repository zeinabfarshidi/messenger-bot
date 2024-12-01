<?php
if (!defined('ABSPATH')) {
    exit;
}

$telegram_token = '7681362529:AAHUjV8JgDlNJWjjsnATUjK9Svujcmjmq_8';
?>

<div class="wrap messenger-settings">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <form method="post" action="options.php">
        <?php settings_fields('messenger_settings'); ?>
        <?php do_settings_sections('messenger_settings'); ?>

        <div class="messenger-settings-tabs">
            <!-- تب‌های تنظیمات -->
            <nav class="nav-tab-wrapper">
                <a href="#telegram-settings" class="nav-tab nav-tab-active">تنظیمات تلگرام</a>
                <a href="#bale-settings" class="nav-tab">تنظیمات بله</a>
                <a href="#soroush-settings" class="nav-tab">تنظیمات سروش</a>
                <a href="#general-settings" class="nav-tab">تنظیمات عمومی</a>
            </nav>

            <!-- تنظیمات تلگرام -->
            <div id="telegram-settings" class="tab-content active">
                <table class="form-table">
                    <tr>
                        <th scope="row">توکن ربات تلگرام</th>
                        <td>
                            <input type="text"
                                   name="messenger_settings[telegram_token]"
                                   value="<?php echo esc_attr($telegram_token); ?>"
                                   class="regular-text"
                                   readonly>
                            <p class="description">توکن دریافتی از @BotFather</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Webhook URL</th>
                        <td>
                            <input type="text"
                                   name="messenger_settings[telegram_webhook]"
                                   value="<?php echo esc_url(site_url('wp-json/messenger/v1/telegram/webhook')); ?>"
                                   class="regular-text"
                                   readonly>
                            <button type="button" class="button" id="set-telegram-webhook">تنظیم Webhook</button>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- تنظیمات بله -->
            <div id="bale-settings" class="tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">توکن ربات بله</th>
                        <td>
                            <input type="text"
                                   name="messenger_settings[bale_token]"
                                   value="<?php echo esc_attr(get_option('messenger_bale_token')); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                </table>
            </div>

            <!-- تنظیمات سروش -->
            <div id="soroush-settings" class="tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">توکن ربات سروش</th>
                        <td>
                            <input type="text"
                                   name="messenger_settings[soroush_token]"
                                   value="<?php echo esc_attr(get_option('messenger_soroush_token')); ?>"
                                   class="regular-text">
                        </td>
                    </tr>
                </table>
            </div>

            <!-- تنظیمات عمومی -->
            <div id="general-settings" class="tab-content">
                <table class="form-table">
                    <tr>
                        <th scope="row">حداکثر تعداد پیام در دقیقه</th>
                        <td>
                            <input type="number"
                                   name="messenger_settings[max_messages_per_minute]"
                                   value="<?php echo esc_attr(get_option('messenger_max_messages_per_minute', 30)); ?>"
                                   min="1"
                                   max="60">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">ذخیره لاگ‌ها</th>
                        <td>
                            <label>
                                <input type="checkbox"
                                       name="messenger_settings[enable_logging]"
                                       value="1"
                                    <?php checked(get_option('messenger_enable_logging', true)); ?>>
                                فعال‌سازی سیستم لاگ
                            </label>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <?php submit_button('ذخیره تنظیمات'); ?>
    </form>
</div>
