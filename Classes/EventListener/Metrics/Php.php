<?php

declare(strict_types=1);

namespace Mfd\Prometheus\EventListener\Metrics;

use Mfd\Prometheus\Event\MetricsCollectingEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener]
class Php
{
    public function __invoke(MetricsCollectingEvent $event): void
    {
        $registry = $event->getRegistry();

        // Memory usage metrics
        $gauge = $registry->getOrRegisterGauge(
            'php',
            'memory_usage_bytes',
            'Current memory usage in bytes'
        );
        $gauge->set(memory_get_usage(true));

        $gauge = $registry->getOrRegisterGauge(
            'php',
            'memory_peak_usage_bytes',
            'Peak memory usage in bytes'
        );
        $gauge->set(memory_get_peak_usage(true));

        if (function_exists('opcache_get_status')) {
            $status = opcache_get_status(false);
            if ($status === false) {
                return;
            }

            $registry->getOrRegisterGauge('php', 'opcache_enabled', 'Whether OPcache is enabled')
                ->set($status['opcache_enabled'] ? 1 : 0);

            $mem = $status['memory_usage'];
            $registry->getOrRegisterGauge('php', 'opcache_memory_used_bytes', 'OPcache used memory in bytes')
                ->set($mem['used_memory']);
            $registry->getOrRegisterGauge('php', 'opcache_memory_free_bytes', 'OPcache free memory in bytes')
                ->set($mem['free_memory']);
            $registry->getOrRegisterGauge('php', 'opcache_memory_wasted_bytes', 'OPcache wasted memory in bytes')
                ->set($mem['wasted_memory']);

            $interned = $status['interned_strings_usage'];
            $registry->getOrRegisterGauge('php', 'opcache_interned_strings_memory_used_bytes', 'OPcache interned strings used memory in bytes')
                ->set($interned['used_memory']);
            $registry->getOrRegisterGauge('php', 'opcache_interned_strings_memory_free_bytes', 'OPcache interned strings free memory in bytes')
                ->set($interned['free_memory']);
            $registry->getOrRegisterGauge('php', 'opcache_interned_strings_total', 'Number of interned strings in OPcache')
                ->set($interned['number_of_strings']);

            $stats = $status['opcache_statistics'];
            $registry->getOrRegisterGauge('php', 'opcache_cached_scripts_total', 'Number of scripts cached in OPcache')
                ->set($stats['num_cached_scripts']);
            $registry->getOrRegisterGauge('php', 'opcache_hits_total', 'Number of OPcache cache hits')
                ->set($stats['hits']);
            $registry->getOrRegisterGauge('php', 'opcache_misses_total', 'Number of OPcache cache misses')
                ->set($stats['misses']);
            $registry->getOrRegisterGauge('php', 'opcache_oom_restarts_total', 'Number of OPcache out-of-memory restarts')
                ->set($stats['oom_restarts']);
            $registry->getOrRegisterGauge('php', 'opcache_hash_restarts_total', 'Number of OPcache hash table overflow restarts')
                ->set($stats['hash_restarts']);
            $registry->getOrRegisterGauge('php', 'opcache_manual_restarts_total', 'Number of manual OPcache restarts')
                ->set($stats['manual_restarts']);
        }
    }
}
