<?php

namespace Mfd\Prometheus\Storage;

use Prometheus\Counter;
use Prometheus\Gauge;
use Prometheus\Histogram;
use Prometheus\Math;
use Prometheus\MetricFamilySamples;
use Prometheus\Storage\Adapter;
use Prometheus\Summary;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

readonly class CachingFrameworkStorage implements Adapter
{
    protected const CACHE_KEY_PREFIX = 'PROMETHEUS_';
    protected const CACHE_KEY_SUFFIX = '_METRICS';

    public function __construct(
        #[Autowire(service: 'cache.prometheus_storage')]
        private FrontendInterface $cacheStorage
    ) {
    }

    public function collect(bool $sortMetrics = true): array
    {
        $metrics = $this->internalCollect(
            $this->fetch(Counter::TYPE),
            $sortMetrics
        );
        $metrics = array_merge(
            $metrics,
            $this->internalCollect($this->fetch(Gauge::TYPE), $sortMetrics)
        );
        $metrics = array_merge(
            $metrics,
            $this->collectHistograms($this->fetch(Histogram::TYPE))
        );
        return array_merge(
            $metrics,
            $this->collectSummaries($this->fetch(Summary::TYPE))
        );
    }

    protected function collectHistograms(array $histograms): array
    {
        $output = [];
        foreach ($histograms as $histogram) {
            $metaData = $histogram['meta'];
            $data = [
                'name' => $metaData['name'],
                'help' => $metaData['help'],
                'type' => $metaData['type'],
                'labelNames' => $metaData['labelNames'],
                'buckets' => $metaData['buckets'],
            ];

            // Add the Inf bucket so we can compute it later on
            $data['buckets'][] = '+Inf';

            $histogramBuckets = [];
            foreach ($histogram['samples'] as $key => $value) {
                $parts = explode(':', (string) $key);
                $labelValues = $parts[2];
                $bucket = $parts[3];
                // Key by labelValues
                $histogramBuckets[$labelValues][$bucket] = $value;
            }

            // Compute all buckets
            $labels = array_keys($histogramBuckets);
            sort($labels);
            foreach ($labels as $labelValues) {
                $acc = 0;
                $decodedLabelValues = $this->decodeLabelValues($labelValues);
                foreach ($data['buckets'] as $bucket) {
                    $bucket = (string)$bucket;
                    if (!isset($histogramBuckets[$labelValues][$bucket])) {
                        $data['samples'][] = [
                            'name' => $metaData['name'] . '_bucket',
                            'labelNames' => ['le'],
                            'labelValues' => array_merge(
                                $decodedLabelValues,
                                [$bucket]
                            ),
                            'value' => $acc,
                        ];
                    } else {
                        $acc += $histogramBuckets[$labelValues][$bucket];
                        $data['samples'][] = [
                            'name' => $metaData['name'] . '_' . 'bucket',
                            'labelNames' => ['le'],
                            'labelValues' => array_merge(
                                $decodedLabelValues,
                                [$bucket]
                            ),
                            'value' => $acc,
                        ];
                    }
                }

                // Add the count
                $data['samples'][] = [
                    'name' => $metaData['name'] . '_count',
                    'labelNames' => [],
                    'labelValues' => $decodedLabelValues,
                    'value' => $acc,
                ];

                // Add the sum
                $data['samples'][] = [
                    'name' => $metaData['name'] . '_sum',
                    'labelNames' => [],
                    'labelValues' => $decodedLabelValues,
                    'value' => $histogramBuckets[$labelValues]['sum'],
                ];
            }

            $output[] = new MetricFamilySamples($data);
        }

        return $output;
    }

    protected function collectSummaries(array $summaries): array
    {
        $math = new Math();
        $output = [];
        foreach ($summaries as $metaKey => &$summary) {
            $metaData = $summary['meta'];
            $data = [
                'name' => $metaData['name'],
                'help' => $metaData['help'],
                'type' => $metaData['type'],
                'labelNames' => $metaData['labelNames'],
                'maxAgeSeconds' => $metaData['maxAgeSeconds'],
                'quantiles' => $metaData['quantiles'],
                'samples' => [],
            ];

            foreach ($summary['samples'] as $key => $values) {
                $parts = explode(':', (string) $key);
                $labelValues = $parts[2];
                $decodedLabelValues = $this->decodeLabelValues($labelValues);

                // Remove old data
                $values = array_filter(
                    $values,
                    static fn(array $value): bool => time() - $value['time']
                        <= $data['maxAgeSeconds']
                );
                if (count($values) === 0) {
                    unset($summary['samples'][$key]);
                    continue;
                }

                // Compute quantiles
                usort($values, static fn(array $value1, array $value2) => $value1['value'] <=> $value2['value']);

                foreach ($data['quantiles'] as $quantile) {
                    $data['samples'][] = [
                        'name' => $metaData['name'],
                        'labelNames' => ['quantile'],
                        'labelValues' => array_merge(
                            $decodedLabelValues,
                            [$quantile]
                        ),
                        'value' => $math->quantile(array_column(
                            $values,
                            'value'
                        ), $quantile),
                    ];
                }

                // Add the count
                $data['samples'][] = [
                    'name' => $metaData['name'] . '_count',
                    'labelNames' => [],
                    'labelValues' => $decodedLabelValues,
                    'value' => count($values),
                ];

                // Add the sum
                $data['samples'][] = [
                    'name' => $metaData['name'] . '_sum',
                    'labelNames' => [],
                    'labelValues' => $decodedLabelValues,
                    'value' => array_sum(array_column($values, 'value')),
                ];
            }

            if (count($data['samples']) > 0) {
                $output[] = new MetricFamilySamples($data);
            }
        }

        return $output;
    }

    protected function internalCollect(
        array $metrics,
        bool $sortMetrics = true
    ): array {
        $result = [];
        foreach ($metrics as $metric) {
            $metaData = $metric['meta'];
            $data = [
                'name' => $metaData['name'],
                'help' => $metaData['help'],
                'type' => $metaData['type'],
                'labelNames' => $metaData['labelNames'],
                'samples' => [],
            ];
            foreach ($metric['samples'] as $key => $value) {
                $parts = explode(':', (string) $key);
                $labelValues = $parts[2];
                $data['samples'][] = [
                    'name' => $metaData['name'],
                    'labelNames' => [],
                    'labelValues' => $this->decodeLabelValues($labelValues),
                    'value' => $value,
                ];
            }

            if ($sortMetrics) {
                $this->sortSamples($data['samples']);
            }

            $result[] = new MetricFamilySamples($data);
        }

        return $result;
    }

    public function updateHistogram(array $data): void
    {
        $histograms = $this->fetch(Histogram::TYPE);

        // Initialize the sum
        $metaKey = $this->metaKey($data);
        if (array_key_exists($metaKey, $histograms) === false) {
            $histograms[$metaKey] = [
                'meta'    => $this->metaData($data),
                'samples' => [],
            ];
        }

        $sumKey = $this->histogramBucketValueKey($data, 'sum');
        if (array_key_exists($sumKey, $histograms[$metaKey]['samples']) === false) {
            $histograms[$metaKey]['samples'][$sumKey] = 0;
        }

        $histograms[$metaKey]['samples'][$sumKey] += $data['value'];


        $bucketToIncrease = '+Inf';
        foreach ($data['buckets'] as $bucket) {
            if ($data['value'] <= $bucket) {
                $bucketToIncrease = $bucket;
                break;
            }
        }

        $bucketKey = $this->histogramBucketValueKey($data, $bucketToIncrease);
        if (array_key_exists($bucketKey, $histograms[$metaKey]['samples'])
            === false
        ) {
            $histograms[$metaKey]['samples'][$bucketKey] = 0;
        }

        $histograms[$metaKey]['samples'][$bucketKey] += 1;

        $this->push(Histogram::TYPE, $histograms);
    }

    public function updateSummary(array $data): void
    {
        $summaries = $this->fetch(Summary::TYPE);

        $metaKey = $this->metaKey($data);
        if (array_key_exists($metaKey, $summaries) === false) {
            $summaries[$metaKey] = [
                'meta'    => $this->metaData($data),
                'samples' => [],
            ];
        }

        $valueKey = $this->valueKey($data);
        if (array_key_exists($valueKey, $summaries[$metaKey]['samples'])
            === false
        ) {
            $summaries[$metaKey]['samples'][$valueKey] = [];
        }

        $summaries[$metaKey]['samples'][$valueKey][] = [
            'time'  => time(),
            'value' => $data['value'],
        ];

        $this->push(Summary::TYPE, $summaries);
    }

    public function updateGauge(array $data): void
    {
        $gauges = $this->fetch(Gauge::TYPE);

        $metaKey = $this->metaKey($data);
        $valueKey = $this->valueKey($data);
        if (array_key_exists($metaKey, $gauges) === false) {
            $gauges[$metaKey] = [
                'meta'    => $this->metaData($data),
                'samples' => [],
            ];
        }

        if (array_key_exists($valueKey, $gauges[$metaKey]['samples'])
            === false
        ) {
            $gauges[$metaKey]['samples'][$valueKey] = 0;
        }

        if ($data['command'] === Adapter::COMMAND_SET) {
            $gauges[$metaKey]['samples'][$valueKey] = $data['value'];
        } else {
            $gauges[$metaKey]['samples'][$valueKey] += $data['value'];
        }

        $this->push(Gauge::TYPE, $gauges);
    }

    public function updateCounter(array $data): void
    {
        $counters = $this->fetch(Counter::TYPE);

        $metaKey = $this->metaKey($data);
        $valueKey = $this->valueKey($data);
        if (array_key_exists($metaKey, $counters) === false) {
            $counters[$metaKey] = [
                'meta'    => $this->metaData($data),
                'samples' => [],
            ];
        }

        if (array_key_exists($valueKey, $counters[$metaKey]['samples'])
            === false
        ) {
            $counters[$metaKey]['samples'][$valueKey] = 0;
        }

        if ($data['command'] === Adapter::COMMAND_SET) {
            $counters[$metaKey]['samples'][$valueKey] = 0;
        } else {
            $counters[$metaKey]['samples'][$valueKey] += $data['value'];
        }

        $this->push(Counter::TYPE, $counters);
    }

    protected function histogramBucketValueKey(array $data, $bucket): string
    {
        return implode(':', [
            $data['type'],
            $data['name'],
            $this->encodeLabelValues($data['labelValues']),
            $bucket,
        ]);
    }

    protected function metaKey(array $data): string
    {
        return implode(':', [
            $data['type'],
            $data['name'],
            'meta'
        ]);
    }

    protected function valueKey(array $data): string
    {
        return implode(':', [
            $data['type'],
            $data['name'],
            $this->encodeLabelValues($data['labelValues']),
            'value'
        ]);
    }

    protected function metaData(array $data): array
    {
        $metricsMetaData = $data;
        unset($metricsMetaData['value'], $metricsMetaData['command'], $metricsMetaData['labelValues']);
        return $metricsMetaData;
    }

    protected function sortSamples(array &$samples): void
    {
        usort($samples, static fn($a, $b): int => strcmp(
            implode("", $a['labelValues']),
            implode("", $b['labelValues'])
        ));
    }

    protected function encodeLabelValues(array $values): string
    {
        $json = json_encode($values, JSON_THROW_ON_ERROR);
        return base64_encode($json);
    }

    protected function decodeLabelValues(string $values): array
    {
        $json = base64_decode($values, true);
        if (false === $json) {
            throw new RuntimeException('Cannot base64 decode label values');
        }

        $decodedValues = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        return $decodedValues;
    }

    protected function fetch(string $type): array
    {
        $result = $this->cacheStorage->get($this->cacheKey($type));
        return $result !== false ? $result : [];
    }

    protected function push(string $type, array $data): void
    {
        $this->cacheStorage->set($this->cacheKey($type), $data, lifetime: 30);
    }

    protected function cacheKey(string $type): string
    {
        return static::CACHE_KEY_PREFIX . $type . static::CACHE_KEY_SUFFIX;
    }

    public function wipeStorage(): void
    {
        $this->cacheStorage->remove($this->cacheKey(Counter::TYPE));
        $this->cacheStorage->remove($this->cacheKey(Gauge::TYPE));
        $this->cacheStorage->remove($this->cacheKey(Histogram::TYPE));
        $this->cacheStorage->remove($this->cacheKey(Summary::TYPE));
    }
}
