<?php
defined('TYPO3_MODE') or die ('Access denied.');

$extconfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['prometheus']);

if ($extconfig['showAdministrationModule'] == true) {
    call_user_func(
        function ($extKey) {
            if (TYPO3_MODE === 'BE') {
                \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                    'Mfc.prometheus',
                    'tools', // Make module a submodule of 'tools'
                    'prometheus', // Submodule key
                    '', // Position
                    [
                        'Backend\Prometheus' => 'getGrafanaContent'
                    ],
                    [
                        'access' => 'user,group',
                        'icon' => 'EXT:' . $extKey . '/Resources/Public/Icon/Icon.svg',
                        'labels' => 'LLL:EXT:' . $extKey . '/Resources/Private/Language/locallang_mod.xml',
                    ]
                );
            }
        },
        $_EXTKEY
    );
}
