<?php
namespace Mfc\MfcProductconfigurator\Service\Excludes;

/**
 * Interface ExcludeInterface
 */
interface ExcludeInterface
{
    /**
     * @param array $settings
     *
     * @return void
     */
    public function setSettings($settings);

    /**
     * @param array $variables
     *
     * @return void
     */
    public function process(&$variables);
}
