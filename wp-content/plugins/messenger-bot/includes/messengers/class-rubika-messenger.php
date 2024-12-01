<?php

if (!defined('ABSPATH')) {
    exit;
}

require_once dirname(__DIR__) . '/class-iranian-messenger.php';

class RubikaMessenger extends IranianMessenger {
    public function __construct($api_key) {
        parent::__construct($api_key);
        $this->messenger_name = 'Rubika';
        $this->base_url = 'https://messengerg2c4.iranlms.ir/';
    }

    public function send_message($chat_id, $message) {
        try {
            $params = [
                'auth' => $this->api_key,
                'chat_id' => $chat_id,
                'text' => $message
            ];

            return $this->make_api_request('sendMessage', $params);
        } catch (Exception $e) {
            $this->log_error('Error sending message: ' . $e->getMessage(), [
                'chat_id' => $chat_id,
                'message' => $message
            ]);
            return false;
        }
    }

    public function set_webhook($webhook_url) {
        try {
            $params = [
                'auth' => $this->api_key,
                'url' => $webhook_url
            ];

            return $this->make_api_request('setWebhook', $params);
        } catch (Exception $e) {
            $this->log_error('Error setting webhook: ' . $e->getMessage(), [
                'webhook_url' => $webhook_url
            ]);
            return false;
        }
    }

    public function process_webhook_request($request) {
        try {
            $data = json_decode($request->get_body(), true);

            if (!$data) {
                throw new Exception('Invalid webhook data received');
            }

            // پردازش داده‌های دریافتی از وبهوک روبیکا
            $message_type = isset($data['type']) ? $data['type'] : '';
            $chat_id = isset($data['chat_id']) ? $data['chat_id'] : '';
            $message = isset($data['message']) ? $data['message'] : '';

            // اینجا می‌توانید پردازش‌های خاص روبیکا را انجام دهید
            return [
                'success' => true,
                'message_type' => $message_type,
                'chat_id' => $chat_id,
                'message' => $message
            ];
        } catch (Exception $e) {
            $this->log_error('Error processing webhook: ' . $e->getMessage(), [
                'request' => $request
            ]);
            return false;
        }
    }

    public function send_file($chat_id, $file_path, $caption = '', $file_type = 'document') {
        try {
            if (!file_exists($file_path)) {
                throw new Exception('File not found: ' . $file_path);
            }

            $mime_type = mime_content_type($file_path);
            $file_name = basename($file_path);

            // تنظیم پارامترهای ارسال فایل
            $params = [
                'auth' => $this->api_key,
                'chat_id' => $chat_id,
                'caption' => $caption,
                'file' => new CURLFile($file_path, $mime_type, $file_name),
                'type' => $file_type
            ];

            // انتخاب endpoint مناسب بر اساس نوع فایل
            $endpoint = match($file_type) {
                'photo' => 'sendPhoto',
                'video' => 'sendVideo',
                'audio' => 'sendAudio',
                default => 'sendDocument'
            };

            return $this->make_api_request($endpoint, $params);
        } catch (Exception $e) {
            $this->log_error('Error sending file: ' . $e->getMessage(), [
                'chat_id' => $chat_id,
                'file_path' => $file_path,
                'file_type' => $file_type
            ]);
            return false;
        }
    }

// متد کمکی برای ارسال تصویر
    public function send_photo($chat_id, $photo_path, $caption = '') {
        return $this->send_file($chat_id, $photo_path, $caption, 'photo');
    }

// متد کمکی برای ارسال ویدیو
    public function send_video($chat_id, $video_path, $caption = '') {
        return $this->send_file($chat_id, $video_path, $caption, 'video');
    }

// متد کمکی برای ارسال صوت
    public function send_audio($chat_id, $audio_path, $caption = '') {
        return $this->send_file($chat_id, $audio_path, $caption, 'audio');
    }

    public function send_message_with_keyboard($chat_id, $message, $keyboard = []) {
        try {
            $params = [
                'auth' => $this->api_key,
                'chat_id' => $chat_id,
                'text' => $message,
                'reply_markup' => [
                    'inline_keyboard' => $keyboard
                ]
            ];

            return $this->make_api_request('sendMessage', $params);
        } catch (Exception $e) {
            $this->log_error('Error sending message with keyboard: ' . $e->getMessage(), [
                'chat_id' => $chat_id,
                'message' => $message,
                'keyboard' => $keyboard
            ]);
            return false;
        }
    }


}
