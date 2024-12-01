<?php
$botToken = '7681362529:AAHUjV8JgDlNJWjjsnATUjK9Svujcmjmq_8';
$domain = 'zfpluginbot.xyz';
$webhookUrl = "https://{$domain}/wp-json/messenger/v1/telegram/webhook";

$telegramApiUrl = "https://api.telegram.org/bot{$botToken}/setWebhook?url={$webhookUrl}";

$result = file_get_contents($telegramApiUrl);
$response = json_decode($result, true);

echo '<pre>';
print_r($response);
echo '</pre>';
