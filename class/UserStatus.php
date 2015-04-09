<?php

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
        Current_User::isLogged() &&
        Current_User::isUnrestricted('hms');
    }

    public static function isUser()
    {
        return self::isMasquerading() ||
        (Current_User::isLogged() &&
        !Current_User::isUnrestricted('hms'));
    }

    public static function isGuest()
    {
        return !Current_User::isLogged();
    }

    public static function isMasquerading()
    {
        return isset($_SESSION['hms_masquerade_username']) || isset($_SESSION['hms_masquerade_as_self']) || isset($_SESSION['hms_masquerade_bannerid']);
    }

    public static function getUsername($respectMask = TRUE)
    {
        if(self::isMasquerading() && $respectMask) {
            return $_SESSION['hms_masquerade_username'];
        }

        return Current_User::getUsername();
    }

    /*public static function getBannerID($respectMask = TRUE)
    {
        if(self::isMasquerading() && $respectMask) {
            //return $_SESSION['hms_masquerade_bannerid'];
            return 111111111;
        }

        //return Current_User::getBannerID();
        return 333333333;
    }*/

    public static function wearMask($username)
    {
        $_SESSION['hms_masquerade_username'] = $username;
        //$_SESSION['hms_masquerade_bannerid'] = $bannerid;
    }

    public static function removeMask()
    {
        unset($_SESSION['hms_masquerade_username']);
        unset($_SESSION['hms_masquerade_bannerid']);
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

    public static function getDisplay()
    {
        $vars = array();
        $user = Current_User::getDisplayName();

        if (UserStatus::isGuest()) {
            // Guest logged in
            $vars['LOGGED_IN_AS'] = dgettext('hms', 'Viewing as Guest');
            $vars['LOGIN_LINK']   = '<a href="/login"><img src="mod/hms/img/tango-icons/actions/edit-redo.png" style="height: 16px; width: 16px; vertical-align: middle;" />ASU WebLogin</a>';
        } else if (UserStatus::isMasquerading() && UserStatus::isMasqueradingAsSelf()) {
            // Masquerading as student version of self
            $vars['LOGGED_IN_AS'] = sprintf(dgettext('sdr', 'Student view for %s'), self::getUsername());
            $vars['OTHERCLASS'] = 'masquerading';
            $cmd = CommandFactory::getCommand('RemoveMaskAsSelf');
            $vars['LOGOUT_LINK'] = $cmd->getLink('Return to Admin View');
        } else if (UserStatus::isMasquerading()) {
            // Masquerading as someone else
            $vars['LOGGED_IN_AS'] = sprintf(dgettext('sdr', 'Masquerading as %s'), self::getUsername());
            $vars['OTHERCLASS'] = 'masquerading';
            $cmd = CommandFactory::getCommand('RemoveMask');
            $vars['LOGOUT_LINK'] = $cmd->getLink('Return to Admin');
        }else if (Current_User::allow('hms', 'ra_login_as_self')) {
            // Allowed to masquerade as self
            $cmd = CommandFactory::getCommand('RaMasqueradeAsSelf');
            $vars['LOGGED_IN_AS'] = sprintf(dgettext('hms', 'Welcome, %s!'), $user) . $cmd->getLink('Swtich to Student View');
            $hms_status = new UserStatus();
            $vars['LOGOUT_LINK']  = $hms_status->getLogoutLink();
        } else {
            $vars['LOGGED_IN_AS'] = sprintf(dgettext('hms', 'Welcome, %s!'), $user);
            $vars['LOGOUT_LINK']  = UserStatus::getLogoutLink();
        }

        return PHPWS_Template::process($vars, 'hms', 'UserStatus.tpl');
    }

    public static function getBigLogin($message = NULL)
    {
        if(!UserStatus::isGuest()) {
            return;
        }

        $vars = array();

        if(!is_null($message))
        $vars['MESSAGE'] = $message;

        $vars['LINK'] = '<a href="/login"><h2><img src="mod/hms/img/tango-icons/actions/edit-redo.png" style="vertical-align: middle" />Log In to HMS</h2></img></a>';

        return PHPWS_Template::process($vars, 'hms', 'UserBigLogin.tpl');
    }

    public function getLogoutLink()
    {
        $auth = Current_User::getAuthorization();
        return '<a href="'.$auth->logout_link.'">Logout</a>';
    }
}

?>
