jQuery(document).ready(function($) {
    // تابع بررسی وضعیت اتصال به API پیام‌رسان‌ها
    function checkMessengerConnection(messengerType) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'check_messenger_connection',
                messenger: messengerType,
                nonce: messengerSettings.nonce
            },
            success: function(response) {
                if (response.success) {
                    showNotification('اتصال به ' + messengerType + ' برقرار است.', 'success');
                } else {
                    showNotification('خطا در اتصال به ' + messengerType, 'error');
                }
            },
            error: function() {
                showNotification('خطا در بررسی اتصال', 'error');
            }
        });
    }

    // نمایش اعلان‌ها
    function showNotification(message, type) {
        var notificationClass = type === 'success' ? 'notice-success' : 'notice-error';
        var notification = $('<div class="notice ' + notificationClass + ' is-dismissible"><p>' + message + '</p></div>');
        $('.wrap.messenger-settings > h1').after(notification);

        setTimeout(function() {
            notification.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }

    // به‌روزرسانی خودکار آمار
    function updateStats() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'get_messenger_stats',
                nonce: messengerSettings.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.stat-box').each(function() {
                        var statType = $(this).data('stat-type');
                        $(this).find('p').text(response.data[statType]);
                    });
                }
            }
        });
    }

    // تست اتصال به پیام‌رسان‌ها
    $('.test-connection').on('click', function(e) {
        e.preventDefault();
        var messengerType = $(this).data('messenger');
        checkMessengerConnection(messengerType);
    });

    // ذخیره تنظیمات با AJAX
    $('#messenger-settings-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData + '&action=save_messenger_settings&nonce=' + messengerSettings.nonce,
            success: function(response) {
                if (response.success) {
                    showNotification('تنظیمات با موفقیت ذخیره شد.', 'success');
                } else {
                    showNotification('خطا در ذخیره تنظیمات.', 'error');
                }
            },
            error: function() {
                showNotification('خطا در ارتباط با سرور.', 'error');
            }
        });
    });

    // به‌روزرسانی خودکار آمار هر 30 ثانیه
    setInterval(updateStats, 30000);

    // تأیید حذف گروه
    $('.delete-group').on('click', function(e) {
        if (!confirm('آیا از حذف این گروه اطمینان دارید؟')) {
            e.preventDefault();
        }
    });

    // نمایش/مخفی کردن توکن‌ها
    $('.toggle-token').on('click', function() {
        var input = $(this).prev('input');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            $(this).text('مخفی کردن');
        } else {
            input.attr('type', 'password');
            $(this).text('نمایش');
        }
    });

    // انتخاب همه گروه‌ها
    $('#select-all-groups').on('click', function() {
        $('.group-checkbox').prop('checked', $(this).prop('checked'));
    });

    // فیلتر کردن گروه‌ها
    $('#group-filter').on('input', function() {
        var searchTerm = $(this).val().toLowerCase();
        $('.group-item').each(function() {
            var groupName = $(this).find('.group-name').text().toLowerCase();
            $(this).toggle(groupName.includes(searchTerm));
        });
    });
});
