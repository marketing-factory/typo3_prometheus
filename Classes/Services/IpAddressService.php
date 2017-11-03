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

        $adressAllowed = false;

        $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['prometheus']);

        if (isset($extConf['allowedIpRanges'])) {
            $adressAllowed = GeneralUtility::cmpIPv4($address, $extConf['allowedIpRanges']);
        }
        return $adressAllowed;
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