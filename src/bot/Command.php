<?php
/**
 * Created by PhpStorm.
 * User: modelfak
 * Date: 09.06.16
 * Time: 16:54
 */

namespace BotTelegram\bot;

use BotTelegram\bot\Entities\Update;
use BotTelegram\bot\BotTelegram as Bot;

abstract class Command {

    public $update = null;
    public $message = null;


    public function __construct(Bot $telegram, Update $update = null)
    {
        $this->telegram = $telegram;
        $this->setUpdate($update);
    }


    /**
     * Set update object
     *
     * @param Entities\Update $update
     * @return Command
     */
    public function setUpdate(Update $update = null)
    {
        if (!empty($update)) {
            $this->update = $update;
            $this->message = $this->update->getMessage();
        }
        return $this;
    }

    /**
     * Get update object
     *
     * @return Entities\Update
     */
    public function getUpdate()
    {
        return $this->update;
    }

    /**
     * Get message object
     *
     * @return Entities\Message
     */
    public function getMessage()
    {
        return $this->message;
    }


    abstract public function execute();

}