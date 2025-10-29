<?php

namespace Mfd\Prometheus\EventListener\Metrics;

use Mfd\Prometheus\Event\MetricsCollectingEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use TYPO3\CMS\Core\Registry;

#[AsEventListener]
readonly class Typo3Scheduler
{
    public function __construct(private Registry $registry)
    {
    }

    public function __invoke(MetricsCollectingEvent $event): void
    {
        $lastRunStartGauge = $event->getRegistry()->getOrRegisterGauge(
            'typo3',
            'scheduler_last_run_start',
            'Start date of the of the TYPO3 scheduler\'s last run'
        );
        $lastRunEndGauge = $event->getRegistry()->getOrRegisterGauge(
            'typo3',
            'scheduler_last_run_end',
            'End date of the of the TYPO3 scheduler\'s last run'
        );
        $lastRunTypeGauge = $event->getRegistry()->getOrRegisterGauge(
            'typo3',
            'scheduler_last_run_type',
            'Type of the of the TYPO3 scheduler\'s last run (0=automatically, 1=manual)'
        );

        $lastRunInfo = $this->registry->get('tx_scheduler', 'lastRun', []);

        $lastRunStartGauge->set($lastRunInfo['start'] ?? 0);
        $lastRunEndGauge->set($lastRunInfo['end'] ?? 0);
        $lastRunTypeGauge->set($lastRunInfo['type'] === 'manual' ? 1 : 0);
    }
}
