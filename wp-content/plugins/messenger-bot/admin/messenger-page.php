<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1>مدیریت پیام‌رسان‌ها</h1>

    <div class="nav-tab-wrapper">
        <a href="?page=messenger-manager&tab=groups" class="nav-tab <?php echo empty($_GET['tab']) || $_GET['tab'] == 'groups' ? 'nav-tab-active' : ''; ?>">گروه‌ها</a>
        <a href="?page=messenger-manager&tab=messages" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] == 'messages' ? 'nav-tab-active' : ''; ?>">پیام‌ها</a>
        <a href="?page=messenger-manager&tab=settings" class="nav-tab <?php echo isset($_GET['tab']) && $_GET['tab'] == 'settings' ? 'nav-tab-active' : ''; ?>">تنظیمات</a>
    </div>

    <div class="tab-content">
        <?php
        $tab = isset($_GET['tab']) ? $_GET['tab'] : 'groups';

        switch($tab) {
            case 'groups':
                include MESSENGER_BOT_PATH . 'admin/tabs/groups.php';
                break;
            case 'messages':
                include MESSENGER_BOT_PATH . 'admin/tabs/messages.php';
                break;
            case 'settings':
                include MESSENGER_BOT_PATH . 'admin/tabs/settings.php';
                break;
        }
        ?>
    </div>
</div>
