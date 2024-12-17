<?php
class TelegramCommands {
    private $telegram;

    public function __construct($telegram) {
        $this->telegram = $telegram;
    }

    public function handle_command($chat_id, $text) {
        $parts = explode(' ', $text, 3);
        $command = strtolower($parts[0]);

        switch ($command) {
            case '/send':
                return $this->send_command($chat_id, $parts);
            default:
                return $this->telegram->send_message($chat_id, 'دستور نامعتبر است.');
        }
    }

    private function send_command($chat_id, $parts) {
        if (count($parts) < 3) {
            return $this->telegram->send_message($chat_id, 'فرمت صحیح: /send chat_id message');
        }

        $target = $parts[1];
        $message = $parts[2];

        $result = $this->telegram->send_message($target, $message);
        $response = $result['ok'] ? 'پیام با موفقیت ارسال شد' : 'خطا در ارسال پیام';

        return $this->telegram->send_message($chat_id, $response);
    }
}
