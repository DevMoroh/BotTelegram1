<?php

namespace BotTelegram\bot\Entities;

use BotTelegram\bot\Exception\TelegramException;

class InlineKeyboardMarkup extends Entity{

    public $inline_keyboard;
    public function __construct($data = array())
    {
        if (isset($data['inline_keyboard'])) {
            if (is_array($data['inline_keyboard'])) {
                foreach ($data['inline_keyboard'] as $item) {
                    if (!is_array($item)) {
                        throw new TelegramException('Inline Keyboard subfield is not an array!');
                    }
                }
                $this->inline_keyboard = $data['inline_keyboard'];
            } else {
                throw new TelegramException('Inline Keyboard field is not an array!');
            }
        } else {
            throw new TelegramException('Inline Keyboard field is empty!');
        }
    }

}