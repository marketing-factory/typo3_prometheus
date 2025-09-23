<?php

$EM_CONF['prometheus'] = [
    'title' => 'TYPO3 Prometheus Metrics',
    'description' => 'Exports Prometheus metrics for TYPO3 instances',
    'category' => 'misc',
    'author' => 'Christian Spoo',
    'author_email' => 'christian.spoo@marketing-factory.de',
    'state' => 'beta',
    'version' => '2.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.9.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
