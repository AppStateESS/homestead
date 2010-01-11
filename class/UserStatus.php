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

	private final function __construct() { }

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
		return isset($_SESSION['hms_masquerade_username']);
	}

	public static function getUsername()
	{
		if(self::isMasquerading()) {
			return $_SESSION['hms_masquerade_username'];
		}

		return Current_User::getUsername();
	}

	public static function wearMask($username)
	{
		$_SESSION['hms_masquerade_username'] = $username;
	}

	public static function removeMask()
	{
		unset($_SESSION['hms_masquerade_username']);
	}

	public static function getDisplay()
	{
		$vars = array();
		$user = Current_User::getDisplayName();

		if(UserStatus::isGuest()) {
			$vars['LOGGED_IN_AS'] = dgettext('hms', 'Viewing as Guest');
			$vars['LOGIN_LINK']   = '<a href="/login"><img src="images/mod/hms/tango-icons/actions/edit-redo.png" style="height: 16px; width: 16px; vertical-align: middle;" />ASU WebLogin</a>';
		} else if(UserStatus::isMasquerading()) {
			$vars['LOGGED_IN_AS'] = sprintf(dgettext('sdr', 'Masquerading as %s'), self::getUsername());
            $vars['OTHERCLASS'] = 'masquerading';
            $cmd = CommandFactory::getCommand('RemoveMask');
            $vars['LOGOUT_LINK'] = $cmd->getLink('Return to Admin');
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

		$vars['LINK'] = '<a href="/login"><h2><img src="images/mod/hms/tango-icons/actions/edit-redo.png" style="vertical-align: middle" />Log In to HMS</h2></img></a>';
			
		return PHPWS_Template::process($vars, 'hms', 'UserBigLogin.tpl');
	}

	public function getLogoutLink()
	{
		$auth = Current_User::getAuthorization();
		return '<a href="'.$auth->logout_link.'">Logout</a>';
	}
}

?>
