<?php
/*
Plugin Name: WP Telegram Bot
Plugin URI:
Description: A Telegram bot plugin for WordPress
Version: 1.0
Author: Your Name
*/

// Site URL Configuration
define('SITE_URL', 'https://zfpluginbot.xyz/');


// Prevent direct access
defined('ABSPATH') or die('No direct access!');

// Bot Configuration
define('BOT_TOKEN', '7929153006:AAFVnLnb-3Vsqz9FYvIZgQh-5NWV1ED5qW0');
define('BOT_USERNAME', '@zeinabToplearnBot');


// Initialize bot
function init_telegram_bot() {
    add_action('rest_api_init', function() {
        register_rest_route('telegram-bot/v1', '/webhook', array(
            'methods' => 'POST',
            'callback' => 'handle_telegram_webhook',
            'url' => SITE_URL . '/wp-json/telegram-bot/v1/webhook'
        ));
    });
}


add_action('init', 'init_telegram_bot');

// Handle incoming messages
// اضافه کردن پردازش دستورات در تابع handle_telegram_webhook
function handle_telegram_webhook($request) {
    $update = json_decode($request->get_body(), true);

    if (isset($update['message'])) {
        $chat_id = $update['message']['chat']['id'];
        $message_text = $update['message']['text'];

        // دستور ارسال به کانال
        if (strpos($message_text, '/send_channel') === 0) {
            $parts = explode(' ', $message_text, 3);
            if (count($parts) >= 3) {
                $channel_id = trim($parts[1]);
                $text = $parts[2];

                $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
                $data = array(
                    'chat_id' => $channel_id,
                    'text' => $text,
                    'parse_mode' => 'HTML'
                );

                wp_remote_post($url, array('body' => $data));
                send_telegram_message($chat_id, "پیام به کانال ارسال شد.");
            }
        }
        // دستور ارسال به گروه
        elseif (strpos($message_text, '/send_group') === 0) {
            $parts = explode(' ', $message_text, 3);
            if (count($parts) >= 3) {
                $group_id = trim($parts[1]);
                $text = $parts[2];

                $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
                $data = array(
                    'chat_id' => $group_id,
                    'text' => $text,
                    'parse_mode' => 'HTML'
                );

                wp_remote_post($url, array('body' => $data));
                send_telegram_message($chat_id, "پیام به گروه ارسال شد.");
            }
        }
        // دستور ارسال به کاربر
        elseif (strpos($message_text, '/send_user') === 0) {
            $parts = explode(' ', $message_text, 3);
            if (count($parts) >= 3) {
                $user_id = trim($parts[1]);
                $text = $parts[2];

                $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
                $data = array(
                    'chat_id' => $user_id,
                    'text' => $text,
                    'parse_mode' => 'HTML'
                );

                wp_remote_post($url, array('body' => $data));
                send_telegram_message($chat_id, "پیام به کاربر ارسال شد.");
            }
        }
        // دستور عضویت در گروه/کانال
        elseif (strpos($message_text, '/join') === 0) {
            $parts = explode(' ', $message_text, 2);
            if (count($parts) == 2) {
                $group_id = trim($parts[1]);
                $result = join_chat($group_id);

                if ($result['ok']) {
                    send_telegram_message($chat_id, "با موفقیت به گروه پیوستم!");
                } else {
                    send_telegram_message($chat_id, "خطا در عضویت: " . $result['description']);
                }
            }
        }
        else {
            send_telegram_message($chat_id, "پیام شما دریافت شد: " . $message_text);
        }
    }

    return new WP_REST_Response('OK', 200);
}

// Send message function
function send_telegram_message($chat_id, $message) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
    $data = array(
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'HTML'
    );

    wp_remote_post($url, array(
        'body' => $data
    ));
}

//از اینجا
// تابع ارسال پیام به گروه یا کانال

function get_chat_id($username) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/getChat";
    $data = array(
        'chat_id' => $username
    );

    $response = wp_remote_post($url, array('body' => $data));
    $result = json_decode(wp_remote_retrieve_body($response), true);

    if ($result['ok']) {
        return $result['result']['id'];
    }
    return false;
}

function send_to_channel($channel_username, $message) {
    $chat_id = get_chat_id($channel_username);
    if ($chat_id) {
        $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
        $data = array(
            'chat_id' => $chat_id,
            'text' => $message,
            'parse_mode' => 'HTML'
        );

        wp_remote_post($url, array('body' => $data));
    }
}

// تابع ارسال پیام به کاربر
function send_to_user($user_id, $message) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
    $data = array(
        'chat_id' => $user_id,
        'text' => $message,
        'parse_mode' => 'HTML'
    );

    wp_remote_post($url, array('body' => $data));
}

// تابع عضو شدن در گروه یا کانال
function join_chat($group_username) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/getChat";
    $data = array(
        'chat_id' => '@' . $group_username
    );

    $response = wp_remote_post($url, array('body' => $data));
    $result = json_decode(wp_remote_retrieve_body($response), true);

    if ($result['ok']) {
        $chat_info = $result['result'];
        save_group_to_database($chat_info['id'], $chat_info['title']);
        return array('ok' => true, 'message' => 'اطلاعات گروه با موفقیت ذخیره شد');
    }

    return $result;
}





// اضافه کردن متاباکس به صفحه دسته‌بندی
add_action('category_add_form_fields', 'add_telegram_groups_field');
add_action('category_edit_form_fields', 'edit_telegram_groups_field');

function add_telegram_groups_field() {
    ?>
    <div class="form-field">
        <label for="telegram_groups">گروه‌های تلگرام</label>
        <select name="telegram_groups[]" id="telegram_groups" multiple>
            <?php
            $groups = get_telegram_groups();
            if (!empty($groups)) {
                foreach($groups as $group) {
                    echo '<option value="' . esc_attr($group['group_id']) . '">' .
                        esc_html($group['group_title']) . '</option>';
                }
            }
            ?>
        </select>
    </div>
    <?php
}

function edit_telegram_groups_field($term) {
    $selected_groups = get_term_meta($term->term_id, 'telegram_groups', true);
    ?>
    <tr class="form-field">
        <th><label for="telegram_groups">گروه‌های تلگرام</label></th>
        <td>
            <select name="telegram_groups[]" id="telegram_groups" multiple>
                <?php
                $groups = get_telegram_groups();
                foreach($groups as $group) {
                    $selected = is_array($selected_groups) && in_array($group['id'], $selected_groups) ? 'selected' : '';
                    echo '<option value="' . esc_attr($group['id']) . '" ' . $selected . '>' . esc_html($group['title']) . '</option>';
                }
                ?>
            </select>
        </td>
    </tr>
    <?php
}

// ایجاد جدول در دیتابیس هنگام فعال‌سازی افزونه
register_activation_hook(__FILE__, 'create_telegram_groups_table');

function create_telegram_groups_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'telegram_groups';

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        group_id VARCHAR(255) NOT NULL,
        group_title VARCHAR(255) NOT NULL,
        join_date DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// ذخیره اطلاعات گروه در دیتابیس
function save_group_to_database($group_data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'telegram_groups';

    $data = array(
        'group_title' => $group_data['group_name'],
        'group_id' => $group_data['invite_link'],
        'description' => $group_data['description'],
        'created_at' => $group_data['created_at']
    );

    $format = array(
        '%s',
        '%s',
        '%s',
        '%s'
    );

    $wpdb->insert($table_name, $data, $format);

    return $wpdb->insert_id;
}


//تابع گرفتن لیست گروه‌ها از تلگرام:
// خواندن لیست گروه‌ها از دیتابیس
function get_telegram_groups() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'telegram_groups';

    $groups = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    return $groups;
}

// ذخیره گروه‌های انتخاب شده
add_action('created_category', 'save_telegram_groups');
add_action('edited_category', 'save_telegram_groups');

function save_telegram_group($group_data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'telegram_groups';

    // تنظیم کاراکترست مناسب برای فارسی
    $wpdb->query("SET NAMES 'utf8mb4'");
    $wpdb->query("SET CHARACTER SET 'utf8mb4'");

    $data = array(
        'group_title' => htmlspecialchars($group_data['group_title'], ENT_QUOTES, 'UTF-8'),
        'group_id' => $group_data['group_id'],
        'created_at' => current_time('mysql')
    );

    $format = array(
        '%s',
        '%s',
        '%s'
    );

    $wpdb->insert($table_name, $data, $format);
    return $wpdb->insert_id;
}


// اضافه کردن استایل به صفحه دسته‌بندی
add_action('admin_head-term.php', 'telegram_groups_style');
add_action('admin_head-edit-tags.php', 'telegram_groups_style');

function telegram_groups_style() {
    ?>
    <style>
        #telegram_groups {
            width: 95%;
            min-height: 150px;
            padding: 8px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #fff;
        }

        #telegram_groups option {
            padding: 8px;
            margin: 2px 0;
            cursor: pointer;
        }

        #telegram_groups option:hover {
            background-color: #f0f0f0;
        }

        #telegram_groups option:checked {
            background-color: #2271b1;
            color: #fff;
        }

        .form-field label {
            font-weight: bold;
            color: #23282d;
            margin-bottom: 5px;
        }
    </style>
    <?php
}

// ارسال خودکار پست به گروه‌های تلگرام
add_action('publish_post', 'send_post_to_telegram_groups', 10, 2);

function send_post_to_telegram_groups($post_id, $post) {
    // فقط برای پست‌های جدید
    if ($post->post_date != $post->post_modified) {
        return;
    }

    // گرفتن دسته‌بندی‌های پست
    $categories = get_the_category($post_id);

    foreach ($categories as $category) {
        // گرفتن گروه‌های مرتبط با هر دسته
        $telegram_groups = get_term_meta($category->term_id, 'telegram_groups', true);

        if (!empty($telegram_groups)) {
            // آماده‌سازی پیام
            $message = "🔔 مطلب جدید\n\n";
            $message .= "📌 " . $post->post_title . "\n\n";
            $message .= "📝 " . wp_trim_words($post->post_content, 20) . "...\n\n";
            $message .= "🔗 " . get_permalink($post_id);

            // ارسال به هر گروه
            foreach ($telegram_groups as $group_id) {
                $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
                $data = array(
                    'chat_id' => $group_id,
                    'text' => $message,
                    'parse_mode' => 'HTML'
                );

                wp_remote_post($url, array('body' => $data));
            }
        }
    }
}

// اضافه کردن منو به پنل ادمین
add_action('admin_menu', 'add_telegram_admin_menu');

function add_telegram_admin_menu() {
    add_menu_page(
        'ارسال پیام به گروه‌ها',
        'پیام رسان تلگرام',
        'manage_options',
        'telegram-messenger',
        'telegram_messenger_page',
        'dashicons-megaphone'
    );
}

function telegram_messenger_page() {
    ?>
    <div class="wrap">
        <h1>ارسال پیام به گروه‌های تلگرام</h1>

        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('send_telegram_message', 'telegram_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">انتخاب گروه‌ها</th>
                    <td>
                        <label>
                            <input type="checkbox" id="select_all" /> انتخاب همه
                        </label>
                        <br/><br/>
                        <?php
                        $groups = get_telegram_groups();
                        foreach($groups as $group) {
                            echo '<label>';
                            echo '<input type="checkbox" name="groups[]" value="' . esc_attr($group['group_id']) . '" /> ';
                            echo esc_html($group['group_title']);
                            echo '</label><br/>';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">متن پیام</th>
                    <td>
                        <textarea name="message" rows="5" cols="50" class="large-text"></textarea>
                    </td>
                </tr>
                <tr>
                    <th scope="row">فایل ضمیمه</th>
                    <td>
                        <input type="file" name="attachment" />
                        <p class="description">پشتیبانی از تصویر، صوت و ویدیو</p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <label style="margin-right: 10px;">
                    <input type="checkbox" name="send_to_members" /> ارسال به اعضای گروه‌ها به صورت دایرکت
                </label>
                <br>
                <br>
                <input type="submit" name="send_message" class="button button-primary" value="ارسال پیام" />
            </p>
        </form>
    </div>

    <script>
        jQuery(document).ready(function($) {
            $('#select_all').change(function() {
                $('input[name="groups[]"]').prop('checked', $(this).prop('checked'));
            });
        });
    </script>
    <?php
}

// پردازش ارسال پیام
add_action('admin_init', 'handle_telegram_message_send');

function handle_telegram_message_send() {
    if (!isset($_POST['send_message'])) {
        return;
    }

    if (!wp_verify_nonce($_POST['telegram_nonce'], 'send_telegram_message')) {
        wp_die('دسترسی غیرمجاز');
    }

    $groups = isset($_POST['groups']) ? $_POST['groups'] : array();
    $message = isset($_POST['message']) ? $_POST['message'] : '';
    $send_to_members = isset($_POST['send_to_members']) ? true : false;

    if (empty($groups) || empty($message)) {
        add_settings_error('telegram_messenger', 'fields_required', 'لطفاً گروه و متن پیام را وارد کنید.');
        return;
    }

    // ارسال پیام به گروه‌ها
    foreach($groups as $group_id) {
        // ارسال به گروه
        $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
        $data = array(
            'chat_id' => $group_id,
            'text' => $message,
            'parse_mode' => 'HTML'
        );

        wp_remote_post($url, array('body' => $data));

        // اگر گزینه ارسال به اعضا فعال باشد
        if ($send_to_members) {
            send_message_to_group_members($group_id, $message);
        }
    }

    // ارسال فایل
    if (!empty($_FILES['attachment']['tmp_name'])) {
        foreach($groups as $group_id) {
            send_telegram_file($group_id, $_FILES['attachment']);

            // ارسال فایل به اعضا
            if ($send_to_members) {
                send_file_to_group_members($group_id, $_FILES['attachment']);
            }
        }
    }

    add_settings_error('telegram_messenger', 'message_sent', 'پیام با موفقیت ارسال شد.', 'success');
}


function send_telegram_file($chat_id, $file) {
    $file_type = wp_check_filetype($file['name']);
    $method = '';

    // تشخیص نوع فایل
    if (strpos($file_type['type'], 'image') !== false) {
        $method = 'sendPhoto';
        $param = 'photo';
    } elseif (strpos($file_type['type'], 'video') !== false) {
        $method = 'sendVideo';
        $param = 'video';
    } elseif (strpos($file_type['type'], 'audio') !== false) {
        $method = 'sendAudio';
        $param = 'audio';
    }

    if ($method) {
        $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/{$method}";
        $post_file = curl_file_create($file['tmp_name'], $file_type['type'], $file['name']);

        $data = array(
            'chat_id' => $chat_id,
            $param => $post_file
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
}

//ارسال کانتکت
function send_bot_contact($chat_id) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendContact";
    $data = array(
        'chat_id' => $chat_id,
        'phone_number' => '+98000000000', // شماره تلفن ربات
        'first_name' => 'ربات زینب',
        'last_name' => 'فرشیدی',
        'vcard' => 'BEGIN:VCARD\nVERSION:3.0\nFN:ربات زینب تاپ لرن\nEND:VCARD'
    );

    wp_remote_post($url, array('body' => $data));
}

//ارسال پیام به اعضای گروه
function send_message_to_group_members($group_id, $message) {
    // دریافت لیست اعضای گروه
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/getChatMembers";
    $data = array('chat_id' => $group_id);

    $response = wp_remote_post($url, array('body' => $data));
    $result = json_decode(wp_remote_retrieve_body($response), true);

    if ($result['ok']) {
        foreach($result['result'] as $member) {
            // ارسال پیام به هر عضو
            $member_id = $member['user']['id'];
            send_telegram_message($member_id, $message);
            // ارسال کانتکت ربات
            send_bot_contact($member_id);
        }
        return true;
    }
    return false;
}

function create_educational_group($group_name, $description, $phone_numbers) {
    $template_group_id = "-1002285469044";

    // ابتدا یک لینک دعوت موقت می‌سازیم
    $invite_url = "https://api.telegram.org/bot" . BOT_TOKEN . "/createChatInviteLink";
    $invite_data = array(
        'chat_id' => $template_group_id,
        'name' => $group_name,
        'creates_join_request' => true,
        'expire_date' => strtotime("+1 day")
    );

    $invite_response = wp_remote_post($invite_url, array(
        'body' => json_encode($invite_data),
        'headers' => array('Content-Type' => 'application/json')
    ));

    $invite_result = json_decode(wp_remote_retrieve_body($invite_response), true);

    if ($invite_result['ok']) {
        // حالا گروه جدید را می‌سازیم
        $create_url = "https://api.telegram.org/bot" . BOT_TOKEN . "/copyMessage";
        $create_data = array(
            'chat_id' => $template_group_id,
            'from_chat_id' => $template_group_id,
            'message_id' => 1,
            'caption' => $description
        );

        $create_response = wp_remote_post($create_url, array(
            'body' => json_encode($create_data),
            'headers' => array('Content-Type' => 'application/json')
        ));

        $create_result = json_decode(wp_remote_retrieve_body($create_response), true);

        // ذخیره اطلاعات گروه جدید
        $new_group_data = array(
            'group_name' => $group_name,
            'invite_link' => $invite_result['result']['invite_link'],
            'description' => $description,
            'created_at' => current_time('mysql')
        );

        save_group_to_database($new_group_data);

        return array(
            'success' => true,
            'message' => "گروه جدید «{$group_name}» ساخته شد!\n" .
                "لینک دعوت: " . $invite_result['result']['invite_link'] . "\n" .
                "لطفاً وارد گروه شوید و تنظیمات را انجام دهید."
        );
    }

    return array(
        'success' => false,
        'message' => "خطا در ساخت گروه: " . print_r($invite_result, true)
    );
}









function create_invite_link($chat_id) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/exportChatInviteLink";
    $data = array('chat_id' => $chat_id);

    $response = wp_remote_post($url, array('body' => $data));
    $result = json_decode(wp_remote_retrieve_body($response), true);

    return $result['ok'] ? $result['result'] : '';
}


function add_create_group_form() {
    ?>
    <div class="wrap">
        <h2>ساخت گروه آموزشی جدید</h2>
        <form method="post" action="">
            <?php wp_nonce_field('create_educational_group', 'group_nonce'); ?>

            <table class="form-table">
                <tr>
                    <th><label for="group_name">نام گروه</label></th>
                    <td>
                        <input type="text" name="group_name" id="group_name" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th><label for="description">توضیحات گروه</label></th>
                    <td>
                        <textarea name="description" id="description" rows="5" class="large-text" required></textarea>
                    </td>
                </tr>
                <tr>
                    <th><label for="phone_numbers">شماره تماس‌ها</label></th>
                    <td>
                        <textarea name="phone_numbers" id="phone_numbers" rows="5" class="large-text" required></textarea>
                        <p class="description">هر شماره را در یک خط جداگانه وارد کنید</p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="create_group" class="button button-primary" value="ساخت گروه">
            </p>
        </form>
    </div>
    <?php
}

// اضافه کردن زیرمنو به پنل ادمین
add_action('admin_menu', 'add_create_group_submenu');

function add_create_group_submenu() {
    add_submenu_page(
        'telegram-messenger',
        'ساخت گروه جدید',
        'ساخت گروه',
        'manage_options',
        'create-telegram-group',
        'add_create_group_form'
    );
}

// پردازش فرم
add_action('admin_init', 'handle_group_creation');

function handle_group_creation() {
    if (isset($_POST['create_group'])) {
        if (!wp_verify_nonce($_POST['group_nonce'], 'create_educational_group')) {
            wp_die('دسترسی غیرمجاز');
        }

        $group_name = sanitize_text_field($_POST['group_name']);
        $description = sanitize_textarea_field($_POST['description']);
        $phone_numbers = array_map('trim', explode("\n", $_POST['phone_numbers']));

        // اضافه کردن کد دیباگ
        error_log('Creating group: ' . $group_name);
        error_log('Phone numbers: ' . print_r($phone_numbers, true));

        $result = create_educational_group($group_name, $description, $phone_numbers);
        error_log('Result: ' . print_r($result, true));

        // نمایش پیام به کاربر
        echo '<div class="notice notice-info is-dismissible">';
        echo '<p>درخواست ساخت گروه دریافت شد. نام گروه: ' . esc_html($group_name) . '</p>';
        echo '</div>';

        if ($result['success']) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p>' . esc_html($result['message']) . '</p>';
            echo '</div>';
        } else {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>' . esc_html($result['message']) . '</p>';
            echo '</div>';
        }
    }
}

function get_group_activity_stats($group_id) {
    // دریافت اطلاعات اعضای گروه
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/getChatAdministrators";
    $data = array(
        'chat_id' => $group_id
    );

    $response = wp_remote_post($url, array(
        'headers' => array('Content-Type' => 'application/json'),
        'body' => json_encode($data)
    ));

    $result = json_decode(wp_remote_retrieve_body($response), true);

    if ($result['ok']) {
        $stats = array();

        // دریافت اطلاعات همه اعضا
        $members_url = "https://api.telegram.org/bot" . BOT_TOKEN . "/getChatMember";

        foreach ($result['result'] as $admin) {
            $member_data = array(
                'chat_id' => $group_id,
                'user_id' => $admin['user']['id']
            );

            $member_response = wp_remote_post($members_url, array(
                'headers' => array('Content-Type' => 'application/json'),
                'body' => json_encode($member_data)
            ));

            $member_result = json_decode(wp_remote_retrieve_body($member_response), true);

            if ($member_result['ok']) {
                $stats[] = array(
                    'name' => $admin['user']['first_name'] . ' ' . ($admin['user']['last_name'] ?? ''),
                    'username' => $admin['user']['username'] ?? '-',
                    'user_id' => $admin['user']['id'],
                    'status' => $member_result['result']['status'],
                    'message_count' => get_user_message_count($group_id, $admin['user']['id'])
                );
            }
        }

        return array(
            'success' => true,
            'data' => $stats
        );
    }

    return array(
        'success' => false,
        'message' => 'خطا در دریافت اطلاعات گروه: ' . ($result['description'] ?? 'خطای نامشخص')
    );
}

function get_user_message_count($chat_id, $user_id) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/getChatMemberCount";
    $data = array(
        'chat_id' => $chat_id,
        'user_id' => $user_id
    );

    $response = wp_remote_post($url, array(
        'headers' => array('Content-Type' => 'application/json'),
        'body' => json_encode($data)
    ));

    $result = json_decode(wp_remote_retrieve_body($response), true);

    if ($result['ok']) {
        return $result['result'];
    }

    return 0;
}






function display_group_stats_page() {
    ?>
    <div class="wrap">
        <h1>آمار فعالیت اعضای گروه</h1>

        <form method="post" action="" class="stats-form" style="margin: 20px 0;">
            <div style="display: flex; gap: 10px; align-items: center;">
                <select name="group_name" style="min-width: 300px; padding: 8px; height: 40px;">
                    <option value="">انتخاب گروه...</option>
                    <?php
                    $groups = get_telegram_groups();
                    if (!empty($groups)) {
                        foreach($groups as $group) {
                            echo '<option value="' . esc_attr($group['group_id']) . '">' .
                                esc_html($group['group_title']) . '</option>';
                        }
                    }
                    ?>
                </select>
                <input type="submit" name="get_stats" class="button button-primary" value="دریافت آمار" style="height: 40px;">
            </div>
        </form>

        <?php
        if (isset($_POST['get_stats']) && !empty($_POST['group_name'])) {
            $stats = get_group_activity_stats($_POST['group_name']);

            if ($stats['success']) {
                ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                    <tr>
                        <th>نام</th>
                        <th>نام کاربری</th>
                        <th>شناسه کاربری</th>
                        <th>تعداد پیام‌ها</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach($stats['data'] as $user): ?>
                        <tr>
                            <td><?php echo esc_html($user['name']); ?></td>
                            <td><?php echo esc_html($user['username']); ?></td>
                            <td><?php echo esc_html($user['user_id']); ?></td>
                            <td><?php echo esc_html($user['message_count']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html($stats['message']) . '</p></div>';
            }
        }
        ?>
    </div>
    <?php
}

// اضافه کردن منو به پنل ادمین
add_action('admin_menu', 'add_group_stats_menu');

function add_group_stats_menu() {
    add_submenu_page(
        'telegram-messenger',
        'آمار فعالیت گروه‌ها',
        'آمار فعالیت',
        'manage_options',
        'telegram-group-stats',
        'display_group_stats_page'
    );
}

//**************************************
//*****************************************
// کلاس اصلی برای مدیریت پیام‌رسان‌ها
class MessengerManager {
    private $messengers = [];

    public function register_messenger($messenger) {
        $this->messengers[$messenger->get_name()] = $messenger;
    }

    public function get_messenger($name) {
        return isset($this->messengers[$name]) ? $this->messengers[$name] : null;
    }
}

// اینترفیس برای پیام‌رسان‌ها
interface MessengerInterface {
    public function get_name();
    public function create_group($group_data);
    public function get_group_stats($group_id);
    public function send_message($chat_id, $message);
}

// کلاس تلگرام
class TelegramMessenger implements MessengerInterface {
    private $bot_token;

    public function __construct($bot_token) {
        $this->bot_token = $bot_token;
    }

    public function get_name() {
        return 'telegram';
    }

    // پیاده‌سازی متدهای تلگرام
}

// کلاس پیام‌رسان ایرانی
class IranianMessenger implements MessengerInterface {
    public function get_name() {
        return 'iranian_messenger';
    }

    // پیاده‌سازی متدهای پیام‌رسان ایرانی
}
