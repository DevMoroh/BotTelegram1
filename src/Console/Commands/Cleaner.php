<?php

namespace BotTelegram\Console\Commands;

use Illuminate\Queue\QueueManager;
use Illuminate\Contracts\Queue\Factory as FactoryContract;

class Clearer
{
    /**
     * @var QueueManager
     */
    protected $manager;
    /**
     * {@inheritDoc}
     */
    function __construct(FactoryContract $manager)
    {
        $this->manager = $manager;
    }
    /**
     * {@inheritDoc}
     */
    public function clear($connection, $queue)
    {
        $count = 0;
        $connection = $this->manager->connection($connection);
        while ($job = $connection->pop($queue)) {
            $job->delete();
            $count++;
        }
        return $count;
    }
}