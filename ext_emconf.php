<?php

$EM_CONF['prometheus'] = array(
    'title' => 'Prometheus TYPO3 connector',
    'description' => '',
    'category' => 'plugin',
    'author' => 'Simon Schmidt',
    'author_email' => 'typo3@marketing-factory.de',
    'module' => '',
    'state' => 'beta',
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 0,
    'version' => '0.0.2',
    'constraints' => array(
        'depends' => array(
            'scheduler' => '',
            'php' => '5.6.7-7.99.99',
            'typo3' => '7.6.23-8.99.99',
        ),
        'conflicts' => array(),

    ),
    'autoload' => array(

        'psr-4' => array(
            'Mfc\\Prometheus\\' => 'Classes/',
        )
    )
);
