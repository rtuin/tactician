<?php

namespace League\Tactician\Plugins;

use League\Tactician\Middleware;

/**
 * If another command is already being executed, locks the command bus and
 * queues the new incoming commands until the first has completed.
 */
class LockingMiddleware implements Middleware
{
    /**
     * @var bool
     */
    private $isExecuting;

    /**
     * @var callable[]
     */
    private $queue = [];

    /**
     * Execute the given command... after other running commands are complete.
     *
     * @param object   $command
     * @param callable $next
     *
     * @return mixed|void
     */
    public function execute($command, callable $next)
    {
        $this->queue[] = $next;
        if ($this->isExecuting) {
            return;
        }

        $this->isExecuting = true;

        $returnValues = [];
        while ($pendingNext = array_shift($this->queue)) {
            $returnValues[] = $pendingNext($command);
        }

        $this->isExecuting = false;
        return array_shift($returnValues);
    }
}
