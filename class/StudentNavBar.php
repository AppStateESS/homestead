<?php

namespace Homestead;

/**
 * StudentNavBar.php
 *
 * @author jbooker
 * @package Homestead
 */
class StudentNavBar extends View {

    private $tpl;

    public function __construct()
    {
    	$this->tpl = array();
    }

    public function show()
    {
        $this->addSignInOut();

        $this->addUserStatus();

        return \PHPWS_Template::process($this->tpl, 'hms', 'studentNavbar.tpl');
    }

    private function addSignInOut()
    {
    	if(UserStatus::isGuest())
        {
        	$this->tpl['SIGNIN_URL'] = './secure';
        } else {
        	$this->tpl['SIGNOUT_URL'] = UserStatus::getLogoutURI();
        }
    }

    private function addUserStatus()
    {
        // If the user is not logged in, then we have nothing to do here
    	  if(UserStatus::isGuest()){
    		    return;
    	  }

				$userTpl = array();

        $userTpl['FULL_NAME'] = UserStatus::getDisplayName();
        $useDropdown = false;

        if (UserStatus::isMasquerading() && UserStatus::isMasqueradingAsSelf()) {
            // User is masquerading as student version of self
            $useDropdown = true;
            $userTpl['FULL_NAME'] = UserStatus::getDisplayName() . ' (student)';
            $cmd = CommandFactory::getCommand('RemoveMaskAsSelf');
            $userTpl['STUDENT_SELF_RETURN'] = $cmd->getURI(); // Link to return to admin version of self
        } else if (UserStatus::isMasquerading()) {
            // User is masquerading as a student
            $useDropdown = true;
            $cmd = CommandFactory::getCommand('RemoveMask');
            $userTpl['REMOVE_MASK'] = $cmd->getURI();

            $displayName = UserStatus::getDisplayName();

            if($displayName == '' || $displayName === null){
                $displayName = UserStatus::getUsername();
            }

            $userTpl['FULL_NAME'] = '<strong class="text-danger">' . $displayName . '</strong>';
        } else if (\Current_User::allow('hms', 'ra_login_as_self')) {
            // User is not masquerading, but do have permission to change to student self-view
            $useDropdown = true;
            $studentViewCmd = CommandFactory::getCommand('RaMasqueradeAsSelf');
            $userTpl['STUDENT_VIEW_URI'] = $studentViewCmd->getURI();
        }

        if($useDropdown){
            // Other options available, so we'll render a drop down
            $this->tpl['USER_STATUS_DROPDOWN'] = \PHPWS_Template::process($userTpl, 'hms', 'UserStatus.tpl');

        }else{
            // No other options, so the user status is just the display name
            $displayName = UserStatus::getDisplayName();

            if($displayName == '' || $displayName === null){
                $displayName = UserStatus::getUsername();
            }

        	$this->tpl['DISPLAY_NAME'] = $displayName;
        }
    }
}
