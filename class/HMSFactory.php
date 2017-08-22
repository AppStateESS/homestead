<?php

namespace Homestead;

use \Homestead\exception\DatabaseException;

class HMSFactory
{
    private static $hms;

    public static function getHMS()
    {
        $rh = getallheaders();

        if (isset(HMSFactory::$hms)) {
            return HMSFactory::$hms;

        } else if (isset($_REQUEST['ajax'])
            || (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
            || isset($_REQUEST['callback'])
            || (array_key_exists('Accept', $rh)
                && stripos($rh['Accept'], 'application/json') !== FALSE)) {
            HMSFactory::$hms = new AjaxHMS();
        } else if (UserStatus::isAdmin()) {
            HMSFactory::$hms = new AdminHMS();
        } else if (UserStatus::isUser()) {
            HMSFactory::$hms = new UserHMS();
        } else {
            // Guest
            HMSFactory::$hms = new GuestHMS();
        }

        return HMSFactory::$hms;
    }
}
