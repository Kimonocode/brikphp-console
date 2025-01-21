<?php

namespace Brikphp\Console\Tests\Server\Mocks;

use React\EventLoop\LoopInterface;
use React\EventLoop\TimerInterface;

class LoopMock implements LoopInterface
{
    private $timers = [];

    public function addTimer($interval, $callback) 
    {
        $timer = $this->createMockTimer($interval, $callback);
        $this->timers[] = $timer;

        // Exécuter immédiatement pour les tests
        $callback();

        return $timer;
    }

    public function addPeriodicTimer($interval, $callback): TimerInterface
    {
        $timer = $this->createMockTimer($interval, $callback, true);
        $this->timers[] = $timer;

        // Exécuter immédiatement pour les tests
        $callback();

        return $timer;
    }

    public function cancelTimer(TimerInterface $timer): void
    {
        foreach ($this->timers as $key => $t) {
            if ($t === $timer) {
                unset($this->timers[$key]);
            }
        }
    }

    public function isTimerActive(TimerInterface $timer): bool
    {
        return in_array($timer, $this->timers, true);
    }

    public function run(): void
    {
        // Pas nécessaire pour les tests
    }

    public function stop(): void
    {
        // Pas nécessaire pour les tests
    }

    /**
     * @inheritDoc
     */
    public function addReadStream($stream, $listener) {
    }
    
    /**
     * @inheritDoc
     */
    public function addSignal($signal, $listener) {
    }
    
    /**
     * @inheritDoc
     */
    public function addWriteStream($stream, $listener) {
    }
    
    /**
     * @inheritDoc
     */
    public function futureTick($listener) {
    }
    
    /**
     * @inheritDoc
     */
    public function removeReadStream($stream) {
    }
    
    /**
     * @inheritDoc
     */
    public function removeSignal($signal, $listener) {
    }
    
    /**
     * @inheritDoc
     */
    public function removeWriteStream($stream) {
    }

    /**
     * @inheritDoc
     */
    private function createMockTimer($interval, callable $callback, $isPeriodic = false): TimerInterface
    {
        return new class($interval, $callback, $isPeriodic) implements TimerInterface {
            private $interval;
            private $callback;
            private $isPeriodic;

            public function __construct($interval, $callback, $isPeriodic)
            {
                $this->interval = $interval;
                $this->callback = $callback;
                $this->isPeriodic = $isPeriodic;
            }

            public function getInterval()
            {
                return $this->interval;
            }

            public function isPeriodic()
            {
                return $this->isPeriodic;
            }
            /**
     * @inheritDoc
     */
    public function getCallback() {
    }
};
    }
}
