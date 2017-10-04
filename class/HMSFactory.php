<?php

PHPWS_Core::initModClass('hms', 'UserStatus.php');
PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'View.php');
PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');

class HMSFactory
{
    private static $hms;

    public static function getHMS()
    {
        //$rh = getallheaders();

        if (isset(HMSFactory::$hms)) {
            return HMSFactory::$hms;

        } else if (isset($_REQUEST['ajax'])
            || (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
            || isset($_REQUEST['callback']) ) {
            //|| (array_key_exists('Accept', $rh)
                //&& stripos($rh['Accept'], 'application/json') !== FALSE)) {
            PHPWS_Core::initModClass('hms', 'AjaxHMS.php');
            HMSFactory::$hms = new AjaxHMS();
        } else if (UserStatus::isAdmin()) {
            PHPWS_Core::initModClass('hms', 'AdminHMS.php');
            HMSFactory::$hms = new AdminHMS();
        } else if (UserStatus::isUser()) {
            PHPWS_Core::initModClass('hms', 'UserHMS.php');
            HMSFactory::$hms = new UserHMS();
        } else {
            // Guest
            PHPWS_Core::initModClass('hms', 'GuestHMS.php');
            HMSFactory::$hms = new GuestHMS();
        }

        return HMSFactory::$hms;
    }
}
