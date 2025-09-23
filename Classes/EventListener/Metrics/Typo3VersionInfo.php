<?php

namespace Mfd\Prometheus\EventListener\Metrics;

use Mfd\Prometheus\Event\MetricsCollectingEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Information\Typo3Version;

#[AsEventListener]
readonly class Typo3VersionInfo
{
    public function __construct(private Typo3Version $typo3Version)
    {
    }

    public function __invoke(MetricsCollectingEvent $event): void
    {
        $gauge = $event->getRegistry()->getOrRegisterGauge(
            'typo3',
            'version_info',
            'TYPO3 version information',
            ['version', 'application_context']
        );

        $gauge->set(1, [
            $this->typo3Version->getVersion(),
            Environment::getContext()->__toString()
        ]);
    }
}
