<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap messenger-help">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="help-container">
        <!-- بخش راهنمای سریع -->
        <div class="help-section quick-start">
            <h2>راهنمای سریع</h2>
            <div class="help-content">
                <h3>شروع کار با پلاگین</h3>
                <ol>
                    <li>تنظیمات پیام‌رسان‌ها را در بخش تنظیمات وارد کنید</li>
                    <li>یک گروه جدید در بخش گروه‌ها ایجاد کنید</li>
                    <li>پیام خود را از بخش پیام‌ها ارسال کنید</li>
                </ol>
            </div>
        </div>

        <!-- بخش سوالات متداول -->
        <div class="help-section faq">
            <h2>سوالات متداول</h2>
            <div class="help-content">
                <div class="faq-item">
                    <h4>چگونه توکن ربات تلگرام را دریافت کنم؟</h4>
                    <div class="faq-answer">
                        <ol>
                            <li>به @BotFather در تلگرام پیام دهید</li>
                            <li>دستور /newbot را ارسال کنید</li>
                            <li>مراحل ساخت ربات را دنبال کنید</li>
                            <li>توکن دریافتی را در تنظیمات پلاگین وارد کنید</li>
                        </ol>
                    </div>
                </div>

                <div class="faq-item">
                    <h4>چگونه یک گروه جدید اضافه کنم؟</h4>
                    <div class="faq-answer">
                        <ol>
                            <li>به بخش "گروه‌ها" بروید</li>
                            <li>روی دکمه "افزودن گروه جدید" کلیک کنید</li>
                            <li>اطلاعات گروه را وارد کنید</li>
                            <li>دکمه "ذخیره" را بزنید</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- بخش مستندات API -->
        <div class="help-section api-docs">
            <h2>مستندات API</h2>
            <div class="help-content">
                <h3>نمونه کد برای ارسال پیام</h3>
                <pre><code>
// ارسال پیام به یک گروه
$messenger = new Messenger_Bot_API();
$result = $messenger->send_message([
    'group_id' => 123,
    'message' => 'متن پیام',
    'type' => 'text'
]);
                </code></pre>

                <h3>هوک‌های موجود</h3>
                <ul>
                    <li><code>messenger_before_send_message</code></li>
                    <li><code>messenger_after_send_message</code></li>
                    <li><code>messenger_message_failed</code></li>
                </ul>
            </div>
        </div>

        <!-- بخش رفع اشکال -->
        <div class="help-section troubleshooting">
            <h2>رفع اشکال</h2>
            <div class="help-content">
                <h3>مشکلات رایج</h3>
                <div class="troubleshoot-item">
                    <h4>خطای اتصال به API</h4>
                    <ul>
                        <li>توکن ربات را بررسی کنید</li>
                        <li>دسترسی به اینترنت را چک کنید</li>
                        <li>تنظیمات فایروال را بررسی کنید</li>
                    </ul>
                </div>

                <div class="troubleshoot-item">
                    <h4>پیام‌ها ارسال نمی‌شوند</h4>
                    <ul>
                        <li>لاگ‌های سیستم را بررسی کنید</li>
                        <li>محدودیت‌های ارسال پیام را چک کنید</li>
                        <li>وضعیت ربات را در پیام‌رسان بررسی کنید</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- بخش پشتیبانی -->
        <div class="help-section support">
            <h2>پشتیبانی</h2>
            <div class="help-content">
                <p>برای دریافت پشتیبانی می‌توانید از روش‌های زیر اقدام کنید:</p>
                <ul>
                    <li>ارسال تیکت پشتیبانی</li>
                    <li>تماس با پشتیبانی در ساعات اداری</li>
                    <li>ارسال ایمیل به آدرس support@example.com</li>
                </ul>
            </div>
        </div>
    </div>
</div>
