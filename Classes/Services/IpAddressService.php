<?php
namespace Mfc\Prometheus\Services;

use TYPO3\CMS\Core\Utility\GeneralUtility;

class IpAddressService
{
    /**
     * @param string $address
     * @return bool
     */
    public function ipInAllowedRange($address = '')
    {
        if (empty($address)) {
            $address = GeneralUtility::getIndpEnv('REMOTE_ADDR');
        }

        $addressAllowed = false;

        $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['prometheus']);

        if (isset($extConf['allowedIpRanges'])) {
            if (GeneralUtility::validIPv4($address)) {
                $addressAllowed = GeneralUtility::cmpIPv4($address, $extConf['allowedIpRanges']);
            } elseif (GeneralUtility::validIPv6($address)) {
                $addressAllowed = GeneralUtility::cmpIPv6($address, $extConf['allowedIpRanges']);
            }
        }
        return $addressAllowed;
    }
}
