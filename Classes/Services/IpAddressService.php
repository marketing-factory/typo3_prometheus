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
            $address = $this->getCurrentIpAddress();
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

    /**
     * @return string
     */
    protected function getCurrentIpAddress()
    {
        $userIp = $_SERVER['REMOTE_ADDR'];

        $forwardedUserIp = GeneralUtility::trimExplode(';', $_SERVER['HTTP_X_FORWARDED_FOR']);
        if (!empty($forwardedUserIp)
            && (preg_match('/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $forwardedUserIp[0], $matches))
        ) {
            if (!empty($matches[0])) {
                $userIp = $matches[0];
            }
        }

        return $userIp;
    }
}