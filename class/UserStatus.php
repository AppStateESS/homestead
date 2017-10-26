<?php

namespace Homestead;

/**
 * HMS User Status
 * Used to quickly determine proper permissioning and displaying the login
 * stuff at the top.  Also used for admins that are masquerading as other
 * user types.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

define('HMS_USERSTATUS_GUEST', 'guest');
define('HMS_USERSTATUS_USER',  'user');
define('HMS_USERSTATUS_ADMIN', 'admin');

class UserStatus
{

    /**
     * Singleton constructor
     */
    private final function __construct() {
        // Empty
    }

    public static function isAdmin()
    {
        return !self::isMasquerading() &&
        \Current_User::isLogged() &&
        \Current_User::isUnrestricted('hms');
    }

    public static function isUser()
    {
        return self::isMasquerading() ||
        (\Current_User::isLogged() &&
        !\Current_User::isUnrestricted('hms'));
    }

    public static function isGuest()
    {
        return !\Current_User::isLogged();
    }

    public static function isMasquerading()
    {
        return isset($_SESSION['hms_masquerade_username']) || isset($_SESSION['hms_masquerade_as_self']);
    }

    public static function getUsername($respectMask = true)
    {
        if(self::isMasquerading() && $respectMask) {
            return $_SESSION['hms_masquerade_username'];
        }

        return \Current_User::getUsername();
    }

    public static function getDisplayName($respectMask = true)
    {
    	if(self::isGuest()){
    		return null;
    	}

        if(self::isMasquerading() && $respectMask) {
            //TODO: Fix the users class so we don't have to query this ourselves....
            $db = new \PHPWS_DB('users');
            $db->addWhere('username', $_SESSION['hms_masquerade_username']);
            $result = $db->select('row');
            return $result['display_name'];
        }

        return \Current_User::getDisplayName();
    }

    public static function wearMask($username)
    {
        $_SESSION['hms_masquerade_username'] = $username;
    }

    public static function removeMask()
    {
        unset($_SESSION['hms_masquerade_username']);
    }


    // For masquerading as self
    public static function wearMaskAsSelf()
    {
        self::wearMask(UserStatus::getUsername());
        $_SESSION['hms_masquerade_as_self'] = UserStatus::getUsername();

    }

    public static function removeMaskAsSelf()
    {
        self::removeMask();
        unset($_SESSION['hms_masquerade_as_self']);
    }

    public static function isMasqueradingAsSelf()
    {
        return isset($_SESSION['hms_masquerade_as_self']);
    }

    public static function getLogoutLink()
    {
        $auth = \Current_User::getAuthorization();
        return '<a href="'.$auth->logout_link.'">Logout</a>';
    }

    public static function getLogoutURI()
    {
    	return \Current_User::getAuthorization()->logout_link;
    }
}
