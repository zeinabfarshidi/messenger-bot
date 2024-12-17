<?php
class TelegramMessenger {
    private $bot_token;

    public function __construct() {
        $this->bot_token = '7681362529:AAHUjV8JgDlNJWjjsnATUjK9Svujcmjmq_8';
    }

    public function addTelegramGroupsField() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'telegram_members';
        $groups = $wpdb->get_results("SELECT * FROM $table_name GROUP BY group_id");

        echo '<div class="form-field">
        <label>گروه‌های تلگرام</label>
        <div style="margin-top: 10px;">';

        foreach($groups as $group) {
            echo '<label style="display: block; margin: 5px 0;">
            <input type="checkbox" name="telegram_groups[]" value="' . esc_attr($group->group_id) . '">
            ' . esc_html($group->group_title) . '
        </label>';
        }

        echo '</div></div>';
    }
    public function editTelegramGroupsField($term) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'telegram_members';
        $groups = $wpdb->get_results("SELECT * FROM $table_name GROUP BY group_id");
        $saved_groups = get_term_meta($term->term_id, 'telegram_members', true);

        echo '<tr class="form-field">
        <th scope="row"><label>گروه‌های تلگرام</label></th>
        <td>';

        foreach($groups as $group) {
            $checked = in_array($group->group_id, json_decode($saved_groups, true)) ? 'checked' : '';
            echo '<label style="display: block; margin: 5px 0;">
            <input type="checkbox" name="telegram_groups[]" value="' . esc_attr($group->group_id) . '" ' . $checked . '>
            ' . esc_html($group->group_title) . '
        </label>';
        }
        echo '</td></tr>';
    }
    public function saveTelegramGroups($term_id) {
        if (isset($_POST['telegram_groups'])) {
            $groups = array_map('sanitize_text_field', $_POST['telegram_groups']);
            $json_groups = json_encode($groups);
            update_term_meta($term_id, 'telegram_members', $json_groups);
        }
    }
    public function displayTelegramGroupsPage() {
        echo '<div class="wrap">';
        echo '<h1>گروه‌های تلگرام</h1>';

        global $wpdb;
        $table_name = $wpdb->prefix . 'telegram_members';
        $groups = $wpdb->get_results("SELECT * FROM $table_name GROUP BY group_id");

        if ($groups) {
            echo '<h3>گروه‌های موجود</h3>';
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>نام گروه</th><th>شناسه گروه</th></tr></thead>';
            echo '<tbody>';
            foreach($groups as $group) {
                echo '<tr>';
                echo '<td>' . esc_html($group->group_title) . '</td>';
                echo '<td>' . esc_html($group->group_id) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        }
        echo '</div>';
    }
    public function sendPostToTelegramGroups($post_id, $post) {
        $content = file_get_contents('php://input');
        $json_decode = json_decode($content, true);
        // اگر پست در حال انتشار نیست، برگرد
        if ($post->post_status !== 'publish') {
            return;
        }
        $token = '7681362529:AAHUjV8JgDlNJWjjsnATUjK9Svujcmjmq_8';
        global $wpdb;
        $category_ids = $json_decode['categories'];
        $categories_name = [];
        $groups_ids = [];
        foreach ($category_ids as $category_id) {
            $category = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}terms WHERE term_id = '$category_id'");
            $categories_name[] = $category->name;
            global $wpdb;
            $termmeta = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}termmeta WHERE term_id = %d",
                $category_id
            ));

            foreach (json_decode(get_term_meta($category_id, 'telegram_members', true), true) as $value){
                $groups_ids[] = $value;
            }
        }

        foreach (array_unique($groups_ids) as $groups_id) {
//            $group_telegram = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}telegram_members WHERE group_id = '$groupsId' ORDER BY id ASC LIMIT 1");
            $message = "📢 مطلب جدید در " . implode(',', $categories_name) . ":\n\n";
            $message .= "🔸 " . $post->post_title . "\n\n";
            $message .= "📝 " . wp_trim_words(strip_tags($post->post_content), 30) . "\n\n";
            $message .= "🔗 " . get_permalink($post_id);

            wp_remote_post("https://api.telegram.org/bot{$token}/sendMessage", [
                'body' => [
                    'chat_id' => $groups_id,
                    'text' => $message
                ]
            ]);
        }
    }
    public function addTelegramMembersReportMenu() {
        add_submenu_page(
            'telegram-groups',
            'گزارش اعضای گروه',
            'گزارش اعضا',
            'manage_options',
            'telegram-members-report',
            [$this, 'displayMembersReportPage']
        );
    }
    public function displayMembersReportPage() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'telegram_members';
        echo '<div class="wrap">';
        echo '<h1>گزارش اعضای گروه تلگرام</h1>';

        // دریافت لیست گروه‌ها از دیتابیس
        $groups = $wpdb->get_results("SELECT * FROM $table_name GROUP BY group_id");
        echo '<form method="post">';
        echo '<select name="selected_group" style="width: 300px; margin-left: 10px">';
        echo "<option value=''>انتخاب گروه تلگرام</option>";
        foreach ($groups as $group) {
            $selected = (isset($_POST['selected_group']) && $_POST['selected_group'] == $group->group_id) ? 'selected' : '';
            echo "<option value='{$group->group_id}' {$selected}>{$group->group_title}</option>";
        }
        echo '</select>';
        echo '<input type="submit" class="button button-primary" value="نمایش اعضا">';
        echo '</form>';

        // اگر گروهی انتخاب شده، اطلاعات اعضا را نمایش بده
        if (isset($_POST['selected_group'])) {
            $group_id = intval($_POST['selected_group']);
            if ($group_id) {
                $this->displayGroupMessages($group_id);
            }
        }

        echo '</div>';
    }
    public function displayGroupMessages($group_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'telegram_members';
        $group_members = $wpdb->get_results("SELECT * FROM $table_name WHERE group_id = {$group_id}");
        echo '<div class="wrap">';
        echo '<h2>پیام‌های اخیر گروه</h2>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th>نام کاربر</th>';
        echo '<th>شناسه کاربر</th>';
        echo '<th>تاریخ آخرین پیام ارسال شده</th>';
        echo '</tr></thead><tbody>';
        foreach ($group_members as $group_member) {
            echo '<tr>';
            echo '<td>' . esc_html($group_member->first_name) . '</td>';
            echo '<td>' . esc_html($group_member->user_id) . '</td>';
            echo '<td>' . esc_html($group_member->last_message_date) . '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        echo '</div>';
        error_log($group_members);
    }
    public function displayTelegramSendMessagePage() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'telegram_members';
        $groups = $wpdb->get_results("SELECT * FROM $table_name GROUP BY group_id");
        ?>
        <div class="wrap">
            <h1>ارسال پیام به گروه‌های تلگرام</h1>
            <form method="post" enctype="multipart/form-data">
                <table class="form-table">
                    <tr>
                        <th>انتخاب گروه‌ها</th>
                        <td>
                            <label>
                                <input type="checkbox" id="select-all"> انتخاب همه
                            </label>
                            <br><br>
                            <?php foreach($groups as $group): ?>
                                <label>
                                    <input type="checkbox" name="groups[]" value="<?php echo $group->group_id; ?>">
                                    <?php echo $group->group_title; ?>
                                </label>
                                <br>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>متن پیام</th>
                        <td>
                            <textarea name="message" rows="5" cols="50" required></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th>فایل (اختیاری)</th>
                        <td>
                            <input type="file" name="attachment">
                        </td>
                    </tr>
                    <tr>
                        <th>ضبط صدا</th>
                        <td>
                            <button type="button" id="startRecord" class="button">شروع ضبط</button>
                            <button type="button" id="stopRecord" class="button" disabled>پایان ضبط</button>
                            <audio id="audioPreview" controls style="display:none"></audio>
                            <input type="hidden" name="audio_data" id="audioData">
                        </td>
                    </tr>
                    <tr>
                        <th>ارسال به اعضای گروه‌ها</th>
                        <td>
                            <button type="button" id="sendToMembers" class="button">ارسال به همه اعضا</button>
                        </td>
                    </tr>
                </table>
                <div id="debug-results"></div>
                <?php wp_nonce_field('send_telegram_message', 'telegram_message_nonce'); ?>
                <input type="submit" name="send_message" class="button button-primary" value="ارسال پیام">
            </form>
        </div>
        <script>
            jQuery(document).ready(function($) {
                let a = $('#select-all').change(function() {
                    $('input[name="groups[]"]').prop('checked', $(this).prop('checked'));
                });
                let mediaRecorder;
                let audioChunks = [];

                $('#startRecord').click(async function() {
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                        mediaRecorder = new MediaRecorder(stream);

                        mediaRecorder.ondataavailable = (event) => {
                            audioChunks.push(event.data);
                        };

                        mediaRecorder.onstop = () => {
                            const audioBlob = new Blob(audioChunks, { type: 'audio/wav' });
                            const audioUrl = URL.createObjectURL(audioBlob);
                            $('#audioPreview').attr('src', audioUrl).show();

                            // تبدیل به Base64 برای ارسال
                            const reader = new FileReader();
                            reader.readAsDataURL(audioBlob);
                            reader.onloadend = () => {
                                $('#audioData').val(reader.result);
                            };
                        };

                        audioChunks = [];
                        mediaRecorder.start();
                        $(this).prop('disabled', true);
                        $('#stopRecord').prop('disabled', false);
                    } catch (err) {
                        alert('خطا در دسترسی به میکروفون: ' + err.message);
                    }
                });

                $('#stopRecord').click(function() {
                    mediaRecorder.stop();
                    $(this).prop('disabled', true);
                    $('#startRecord').prop('disabled', false);
                });

                // برای لاگ
                $('#sendToMembers').click(function() {
                    $.post(ajaxurl, {
                        action: 'send_to_members'
                    }, function(response) {
                        // نمایش نتایج در صفحه
                        $('#debug-results').html(response);
                    });
                });
            });
        </script>
        <?php
    }
    public function processingOfSendingMessagesToTelegramGroups()
    {
        if (isset($_POST['send_message']) && check_admin_referer('send_telegram_message', 'telegram_message_nonce')) {
            $token = '7681362529:AAHUjV8JgDlNJWjjsnATUjK9Svujcmjmq_8';
            $groups = isset($_POST['groups']) ? $_POST['groups'] : [];
            $message = sanitize_textarea_field($_POST['message']);
            if (!empty($groups) && !empty($message)) {
                foreach ($groups as $group_id) {
                    // ارسال متن
                    wp_remote_post("https://api.telegram.org/bot{$token}/sendMessage", [
                        'body' => [
                            'chat_id' => $group_id,
                            'text' => $message
                        ]
                    ]);

                    // اگر فایل آپلود شده باشد
                    if (!empty($_FILES['attachment']['tmp_name'])) {
                        $file_path = $_FILES['attachment']['tmp_name'];
                        $file_type = wp_check_filetype($_FILES['attachment']['name'])['type'];
                        error_log('File type: ' . $file_type); // اضافه کردن لاگ

                        // تشخیص نوع فایل و ارسال
                        if (strpos($file_type, 'image') !== false) {
                            $endpoint = 'sendPhoto';
                            $param = 'photo';
                        } elseif (strpos($file_type, 'video') !== false) {
                            $endpoint = 'sendVideo';
                            $param = 'video';
                        } elseif (strpos($file_type, 'audio') !== false ||
                            strpos($file_type, 'mpeg') !== false ||
                            strpos($file_type, 'mp3') !== false) {
                            $endpoint = 'sendAudio';
                            $param = 'audio';
                            error_log('Sending as audio file'); // اضافه کردن لاگ
                        }

                        if (isset($endpoint)) {
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$token}/{$endpoint}");
                            curl_setopt($ch, CURLOPT_POST, 1);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                                'chat_id' => $group_id,
                                $param => new CURLFile($file_path)
                            ]);
                            $response = curl_exec($ch);
                            curl_close($ch);
                        }
                    }
                    // کد پردازش صدای ضبط شده
                    if (isset($_POST['audio_data']) && !empty($_POST['audio_data'])) {
                        $audio_data = $_POST['audio_data'];
                        $audio_data = str_replace('data:audio/wav;base64,', '', $audio_data);
                        $audio_data = base64_decode($audio_data);

                        // ذخیره موقت فایل صوتی
                        $temp_file = wp_tempnam('audio_message');
                        file_put_contents($temp_file, $audio_data);

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$token}/sendAudio");
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, [
                            'chat_id' => $group_id,
                            'audio' => new CURLFile($temp_file, 'audio/wav', 'audio_message.wav')
                        ]);
                        $response = curl_exec($ch);
                        curl_close($ch);

                        // پاک کردن فایل موقت
                        unlink($temp_file);
                    }
                }
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success"><p>پیام با موفقیت ارسال شد.</p></div>';
                });
            }
        }
    }
    public function sendBotContact($chat_id, $bot_name) {
        $token = '7681362529:AAHUjV8JgDlNJWjjsnATUjK9Svujcmjmq_8';

        $response = wp_remote_post("https://api.telegram.org/bot{$token}/sendContact", [
            'body' => [
                'chat_id' => $chat_id,
                'phone_number' => '+98xxxxxxxxxx', // شماره تلفن ربات
                'first_name' => $bot_name,
                'last_name' => 'Bot',
                'vcard' => "BEGIN:VCARD\nVERSION:3.0\nFN:{$bot_name}\nEND:VCARD"
            ]
        ]);
    }
    public function sendDirectMessageToMembers() {
        $token = '7681362529:AAHUjV8JgDlNJWjjsnATUjK9Svujcmjmq_8';
        global $wpdb;
        $debug_output = [];
        $bot_info = wp_remote_get("https://api.telegram.org/bot{$token}/getMe");
        $bot_data = json_decode(wp_remote_retrieve_body($bot_info));
        $bot_name = $bot_data->result->first_name;
        $debug_output[] = 'نام ربات: ' . $bot_name;

        // دریافت لیست اعضای منحصر به فرد از همه گروه‌ها

        $members = $wpdb->get_results("
        SELECT DISTINCT user_id, first_name, username
        FROM {$wpdb->prefix}telegram_members
        WHERE user_id != {$bot_data->result->id}
    ");

        $debug_output[] = 'تعداد کل اعضا: ' . count($members);

        foreach ($members as $member) {
            // ارسال پیام به هر عضو
            $message_response = wp_remote_post("https://api.telegram.org/bot{$token}/sendMessage", [
                'body' => [
                    'chat_id' => $member->user_id,
//                    'text' => "سلام {$member->first_name}! من ربات {$bot_name} هستم.",
                    'text' => $this->sendBotContact($member->user_id, $bot_name)
                ]
            ]);

            $debug_output[] = "ارسال پیام به کاربر {$member->first_name}";
        }

        echo '<div class="debug-output" style="background: #f5f5f5; padding: 15px; margin: 20px 0; border: 1px solid #ddd;">' .
            '<h3>گزارش عملیات:</h3>' .
            '<pre>' . implode("\n", $debug_output) . '</pre>' .
            '</div>';
    }
    public function registerPortfolioPostType() {
        register_post_type('portfolio', array(
            'labels' => array(
                'name' => 'نمونه کارها',
                'singular_name' => 'نمونه کار',
                'add_new' => 'افزودن نمونه کار',
                'add_new_item' => 'افزودن نمونه کار جدید',
                'edit_item' => 'ویرایش نمونه کار',
                'all_items' => 'همه نمونه کارها'
            ),
            'public' => true,
            'has_archive' => true,
            'supports' => array('title', 'thumbnail'),
            'menu_icon' => 'dashicons-portfolio',
            'publicly_queryable' => true,
            'show_in_nav_menus' => true,
            'rewrite' => array('slug' => 'portfolio')
        ));
    }
    public function addPortfolioFileMetabox() {
        add_meta_box(
            'portfolio_file',
            'فایل نمونه کار',
            'render_portfolio_file_metabox',
            'portfolio',
            'normal',
            'high'
        );
    }
    public function renderPortfolioFileMetabox($post) {
        wp_nonce_field('save_portfolio_file', 'portfolio_file_nonce');
        $file_url = get_post_meta($post->ID, '_portfolio_file', true);
        ?>
        <div>
            <input type="text" id="portfolio_file" name="portfolio_file" value="<?php echo esc_attr($file_url); ?>" style="width:80%">
            <button type="button" class="button" id="upload_file_button">انتخاب فایل</button>
            <?php if ($file_url): ?>
                <a href="<?php echo esc_url($file_url); ?>" target="_blank">مشاهده فایل</a>
            <?php endif; ?>
        </div>
        <script>
            jQuery(document).ready(function($){
                $('#upload_file_button').click(function(e) {
                    e.preventDefault();
                    var custom_uploader = wp.media({
                        title: 'انتخاب فایل نمونه کار',
                        button: {
                            text: 'انتخاب'
                        },
                        multiple: false
                    });
                    custom_uploader.on('select', function() {
                        var attachment = custom_uploader.state().get('selection').first().toJSON();
                        $('#portfolio_file').val(attachment.url);
                    });
                    custom_uploader.open();
                });
            });
        </script>
        <?php
    }
    public function savePortfolioFile($post_id) {
        if (!isset($_POST['portfolio_file_nonce']) ||
            !wp_verify_nonce($_POST['portfolio_file_nonce'], 'save_portfolio_file')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (isset($_POST['portfolio_file'])) {
            update_post_meta($post_id, '_portfolio_file', sanitize_text_field($_POST['portfolio_file']));
        }
    }
    public function addTelegramNotificationButton($content) {
        if (is_singular('portfolio')) {
            $button = '<div class="telegram-notify-button">';
            $button .= '<button type="button" class="button" onclick="openTelegramNotifyForm()">اطلاع‌رسانی در تلگرام</button>';
            $button .= '</div>';

            // فرم ارسال پیام
            $button .= '<div id="telegram-notify-form" style="display:none;">';
            $button .= '<h3>ارسال پیام به گروه‌های تلگرام</h3>';
            $button .= '<form method="post" enctype="multipart/form-data">';
            $button .= wp_nonce_field('telegram_notify', 'telegram_notify_nonce', true, false);
            $button .= '<textarea name="message" placeholder="متن پیام" style="width: 100%; height: 150px; padding: 15px"></textarea><br>';
            $button .= '<div style="margin: 20px 0"><input type="file" name="attachment"></div>';
            $button .= '<button type="submit" name="send_telegram" class="button">ارسال پیام</button>';
            $button .= '</form>';
            $button .= '</div>';

            $content .= $button;
        }
        return $content;
    }
    public function addTelegramNotifyScripts() {
        if (is_singular('portfolio')) {
            ?>
            <script>
                function openTelegramNotifyForm() {
                    var form = document.getElementById('telegram-notify-form');
                    form.style.display = form.style.display === 'none' ? 'block' : 'none';
                }
            </script>
            <?php
        }
    }
    public function registerPortfolioTaxonomy() {
        register_taxonomy(
            'portfolio_category',
            'portfolio',
            array(
                'labels' => array(
                    'name' => 'دسته‌بندی نمونه کارها',
                    'singular_name' => 'دسته‌بندی نمونه کار',
                    'add_new_item' => 'افزودن دسته‌بندی جدید',
                    'edit_item' => 'ویرایش دسته‌بندی',
                    'all_items' => 'همه دسته‌بندی‌ها'
                ),
                'hierarchical' => true,
                'show_ui' => true,
                'show_admin_column' => true,
                'query_var' => true,
                'rewrite' => array('slug' => 'portfolio-category')
            )
        );
    }
    public function addTelegramGroupsToPortfolioCategory($term = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'telegram_members';
        $groups = $wpdb->get_results("SELECT * FROM $table_name GROUP BY group_id");

        // دریافت گروه‌های انتخاب شده
        $selected_groups = [];
        if ($term && isset($term->term_id)) {
            $selected_groups = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}termmeta WHERE term_id =  '$term->term_id' LIMIT 1");
            if (!is_array($selected_groups)) {
                $selected_groups = array();
            }
        }

        // برای صفحه ویرایش
        if ($term) {
            ?>
            <tr class="form-field">
                <th scope="row"><label>گروه‌های تلگرام مرتبط</label></th>
                <td>
                    <div style="max-height: 200px; overflow-y: auto; padding: 10px; border: 1px solid #ddd;">
                        <?php
                        $group_ids = json_decode($selected_groups[0]->meta_value, true);
                        foreach ($groups as $group): ?>
                            <label style="display: block; margin-bottom: 5px;">
                                <input type="checkbox"
                                       name="telegram_groups[]"
                                       value="<?php echo $group->group_id; ?>"
                                    <?php echo in_array($group->group_id, $group_ids) ? 'checked' : ''; ?>>
                                <?php echo $group->group_title; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="description">گروه‌های تلگرامی که با این دسته‌بندی مرتبط هستند را انتخاب کنید.</p>
                </td>
            </tr>
            <?php
        }
        // برای صفحه افزودن دسته‌بندی جدید
        else {
            ?>
            <div class="form-field">
                <label>گروه‌های تلگرام مرتبط</label>
                <div style="max-height: 200px; overflow-y: auto; padding: 10px; border: 1px solid #ddd;">
                    <?php foreach ($groups as $group): ?>
                        <label style="display: block; margin-bottom: 5px;">
                            <input type="checkbox" name="telegram_groups[]" value="<?php echo $group->group_id; ?>">
                            <?php echo $group->group_title; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <p>گروه‌های تلگرامی که با این دسته‌بندی مرتبط هستند را انتخاب کنید.</p>
            </div>
            <?php
        }
    }
    public function savePortfolioTelegramGroups($term_id) {
        if (isset($_POST['telegram_groups'])) {
            $groups = array_map('intval', $_POST['telegram_groups']);
            update_term_meta($term_id, 'telegram_groups', json_encode($groups));
        }
    }
    public function processPortfolioTelegramNotification() {
        if (isset($_POST['send_telegram']) && isset($_POST['telegram_notify_nonce'])) {
            if (!wp_verify_nonce($_POST['telegram_notify_nonce'], 'telegram_notify')) {
                return;
            }

            $post_id = get_the_ID();
            $post_title = get_the_title($post_id);
            $post_link = get_permalink($post_id);
            $message = isset($_POST['message']) ? $_POST['message'] : '';
            $voice_message = isset($_POST['voice_message']) ? $_POST['voice_message'] : '';

            $final_message = $message . "\n\n";
            $final_message .= "عنوان: " . $post_title . "\n";
            $final_message .= "لینک: " . $post_link;

            $categories = get_the_terms($post_id, 'portfolio_category');

            if ($categories) {
                foreach ($categories as $category) {
                    $telegram_groups = get_term_meta($category->term_id, 'telegram_groups', true);
                    if (is_array(json_decode($telegram_groups, true))) {
                        foreach (json_decode($telegram_groups, true) as $group_id) {
                            // ارسال پیام متنی
                            $this->sendMessageToTelegram($group_id, $final_message);

                            // ارسال پیام صوتی
                            if (!empty($voice_message)) {
                                $this->sendVoiceToTelegram($group_id, $voice_message);
                            }
                        }
                    }
                }
            }
        }
    }
    public function sendMessageToTelegram($group_id, $message) {
        global $wpdb;
        $group = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}telegram_groups WHERE group_id = %s",
            $group_id
        ));

        if ($group) {
            $bot_token = '7681362529:AAHUjV8JgDlNJWjjsnATUjK9Svujcmjmq_8';
            $chat_id = $group->group_id;
            $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
            $args = array(
                'body' => array(
                    'chat_id' => $chat_id,
                    'text' => $message,
                    'parse_mode' => 'HTML'
                )
            );

            wp_remote_post($url, $args);
        }
    }
    public function sendTelegramMessage($group_id, $message) {
        global $wpdb;
        // دریافت اطلاعات گروه از دیتابیس
        $group = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}telegram_members WHERE group_id = %s",
            $group_id
        ));
        if ($group) {
            $bot_token = '7681362529:AAHUjV8JgDlNJWjjsnATUjK9Svujcmjmq_8';
            $chat_id = $group->chat_id;

            $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
            $args = array(
                'body' => array(
                    'chat_id' => $chat_id,
                    'text' => $message,
                    'parse_mode' => 'HTML'
                )
            );

            $response = wp_remote_post($url, $args);

            if (is_wp_error($response)) {
                error_log('Telegram API Error: ' . $response->get_error_message());
            }
        }
    }
    public function sendTelegramFile($group_id, $file_url) {
        global $wpdb;
        $group = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}telegram_groups WHERE group_id = %s",
            $group_id
        ));

        if ($group) {
            $bot_token = get_option('telegram_bot_token');
            $chat_id = $group->chat_id;

            // تشخیص نوع فایل
            $file_type = wp_check_filetype($file_url);
            $method = 'sendDocument';

            if (strpos($file_type['type'], 'image') !== false) {
                $method = 'sendPhoto';
            } elseif (strpos($file_type['type'], 'video') !== false) {
                $method = 'sendVideo';
            } elseif (strpos($file_type['type'], 'audio') !== false) {
                $method = 'sendAudio';
            }

            $url = "https://api.telegram.org/bot{$bot_token}/{$method}";
            $args = array(
                'body' => array(
                    'chat_id' => $chat_id,
                    'caption' => '',
                    $method === 'sendPhoto' ? 'photo' : 'document' => $file_url
                )
            );

            wp_remote_post($url, $args);
        }
    }
    public function addPortfolioCategoriesToContent($content) {
        if (is_singular('portfolio')) {
            $categories = get_the_terms(get_the_ID(), 'portfolio_category');
            $category_names = [];
            foreach ($categories as $category) {
                $category_names[] = $category->name;
            }
            $categories_str = implode(', ', $category_names);
            if ($categories) {
                $categories_html = '<div class="portfolio-categories">';
                $categories_html .= '<strong>دسته‌بندی‌ها: </strong>' . $categories_str;
                $categories_html .= '</div>';
                $content = $categories_html . $content;
            }
        }
        return $content;
    }
    public function addVoiceRecorderToPortfolioForm($content) {
        if (is_singular('portfolio')) {
            $recorder_html = '
        <div class="voice-recorder-container">
            <button type="button" id="startRecord">شروع ضبط</button>
            <button type="button" id="stopRecord" style="display:none;">پایان ضبط</button>
            <div id="recordingStatus"></div>
            <audio id="recordedAudio" controls style="display:none;"></audio>
            <input type="hidden" name="voice_message" id="voice_message">
        </div>
        <script>
            let mediaRecorder;
            let audioChunks = [];
            
            document.getElementById("startRecord").addEventListener("click", function() {
                navigator.mediaDevices.getUserMedia({ audio: true })
                    .then(stream => {
                        mediaRecorder = new MediaRecorder(stream);
                        mediaRecorder.start();
                        
                        document.getElementById("startRecord").style.display = "none";
                        document.getElementById("stopRecord").style.display = "inline-block";
                        document.getElementById("recordingStatus").textContent = "در حال ضبط...";
                        
                        audioChunks = [];
                        mediaRecorder.addEventListener("dataavailable", event => {
                            audioChunks.push(event.data);
                        });
                        
                        mediaRecorder.addEventListener("stop", () => {
                            const audioBlob = new Blob(audioChunks, { type: "audio/wav" });
                            const audioUrl = URL.createObjectURL(audioBlob);
                            const audio = document.getElementById("recordedAudio");
                            audio.src = audioUrl;
                            audio.style.display = "block";
                            
                            // تبدیل Blob به Base64
                            const reader = new FileReader();
                            reader.readAsDataURL(audioBlob);
                            reader.onloadend = function() {
                                const base64data = reader.result;
                                document.getElementById("voice_message").value = base64data;
                            }
                        });
                    });
            });
            
            document.getElementById("stopRecord").addEventListener("click", function() {
                mediaRecorder.stop();
                document.getElementById("startRecord").style.display = "inline-block";
                document.getElementById("stopRecord").style.display = "none";
                document.getElementById("recordingStatus").textContent = "ضبط به پایان رسید";
            });
        </script>';

            // اضافه کردن recorder قبل از دکمه ارسال
            $content = str_replace('</form>', $recorder_html . '</form>', $content);
        }
        return $content;
    }
    public function sendVoiceToTelegram($group_id, $voice_base64) {
        global $wpdb;
        $group = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}telegram_groups WHERE group_id = %s",
            $group_id
        ));

        if ($group && !empty($voice_base64)) {
            $bot_token = '7681362529:AAHUjV8JgDlNJWjjsnATUjK9Svujcmjmq_8';
            $chat_id = $group->chat_id;

            $voice_data = str_replace('data:audio/wav;base64,', '', $voice_base64);
            $voice_data = str_replace(' ', '+', $voice_data);
            $voice_binary = base64_decode($voice_data);

            $temp_file = tempnam(sys_get_temp_dir(), 'voice');
            file_put_contents($temp_file, $voice_binary);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot{$bot_token}/sendVoice");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                'chat_id' => $chat_id,
                'voice' => new CURLFile($temp_file, 'audio/wav', 'voice.wav')
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60); // تنظیم تایم‌اوت به 60 ثانیه

            $result = curl_exec($ch);
            curl_close($ch);

            unlink($temp_file);
        }
    }
    public function processTelegramWebhook() {
        $content = file_get_contents('php://input');
        $json_decode = json_decode($content, true);
        $text = $json_decode["message"]["text"];
        $sender_chat_id = $json_decode["message"]["chat"]["id"];
        $explode = explode(' ', $text);
        if ($explode[0] == '/send') {
            $chat_id = $explode[1];
            unset($explode[0]);
            unset($explode[1]);
            $text_send = implode(' ', $explode);
            $this->sendMessage($chat_id, $text_send, $sender_chat_id);
        }
    }
    public function sendMessage($chat_id, $text_send, $sender_chat_id) {
        $param = "chat_id=" . $chat_id . "&text=" . $text_send . "&parse_mode=HTML";
        $url = "https://api.telegram.org/bot{$this->bot_token}/sendMessage?" . $param;

        $result = file_get_contents($url);

        if ($result) {
            $success_param = "chat_id=" . $sender_chat_id . "&text=پیام با موفقیت ارسال شد ✅";
            file_get_contents("https://api.telegram.org/bot{$this->bot_token}/sendMessage?" . $success_param);
        } else {
            $error_param = "chat_id=" . $sender_chat_id . "&text=خطا: کاربر مورد نظر باید ابتدا ربات را استارت کند ❌";
            file_get_contents("https://api.telegram.org/bot{$this->bot_token}/sendMessage?" . $error_param);
        }
    }
    public function createTelegramMembersTable() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'telegram_members';
        // ایجاد جدول اگر وجود نداشت
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        group_id bigint(20) NOT NULL,
        group_title varchar(255) NOT NULL,
        user_id bigint(20) NOT NULL,
        username varchar(255),
        first_name varchar(255),
        last_message_date datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY unique_member (user_id, group_id)
    ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // اضافه کردن ستون جدید بعد از group_id
//    $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$table_name} LIKE 'group_title'");
//    if (empty($column_exists)) {
//        $wpdb->query("ALTER TABLE {$table_name} ADD group_title varchar(255) NOT NULL AFTER group_id");
//    }
    }
    public function handleTelegramWebhook() {
        $input = file_get_contents('php://input');
        $update = json_decode($input, true);
        // ذخیره اطلاعات کاربر
        $this->saveTelegramMember($update);
        // پردازش پیام‌های دریافتی
        if (isset($update['message'])) {
            $message = $update['message'];
            $chat_id = $message['chat']['id'];
            $text = isset($message['text']) ? $message['text'] : '';

            // اینجا می‌توانید پردازش‌های دیگر روی پیام دریافتی انجام دهید
            // مثلاً پاسخ به دستورات خاص
            if ($text == '/start') {
                // پاسخ به دستور start
            }
        }
        echo 'OK';
        exit;
    }
    public function saveTelegramMember($update) {
        $content = file_get_contents('php://input');
        $json_decode = json_decode($content, true);
        global $wpdb;
        $table_name = $wpdb->prefix . 'telegram_members';

        if (isset($json_decode['message']['left_chat_member'])) {
            $user_id = $json_decode['message']['left_chat_member']['id'];
            $chat_id = $json_decode['message']['chat']['id'];
            $wpdb->delete(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'group_id' => $chat_id
                ),
                array('%d', '%s')
            );

            error_log($json_decode['message']['left_chat_member']['first_name'] . ' از گروه ' . $json_decode['message']['chat']['title'] . ' حذف شد');
        }
        else {
            if ($json_decode['message']['new_chat_member'])
                $user = $json_decode['message']['new_chat_member'];
            else
                $user = $json_decode['message']['from'];
            $chat = $json_decode['message']['chat'];

            // فقط برای پیام‌های گروه
            if ($chat['type'] == 'supergroup' || $chat['type'] == 'group') {
                $result = $wpdb->replace(
                    $wpdb->prefix . 'telegram_members',
                    array(
                        'group_id' => $chat['id'],
                        'group_title' => $chat['title'],
                        'user_id' => $user['id'],
                        'username' => isset($user['username']) ? $user['username'] : '',
                        'first_name' => $user['first_name'],
                        'last_message_date' => current_time('mysql')
                    ),
                    array('%d', '%s', '%d', '%s', '%s', '%s')
                );
                error_log('به گروه اضافه شد');
            }
        }
    }
}

