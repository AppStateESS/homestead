<?php

namespace Homestead;

/**
 * @author Jeremy Booker
 * @package Homestead
 */
class TopNavBar extends View {

    private $tpl;

    public function __construct(){
        $this->tpl = array();
    }

    public function show()
    {
        $this->addSignInOut();

        $this->addUserStatus();

        // Add the term selector drop-down if the user is an admin
        if(UserStatus::isAdmin()) {
        	$this->addTermSelector();
        }

        if(\Current_User::allow('hms','search')) {
            $this->addSearch();
        }

        return \PHPWS_Template::process($this->tpl, 'hms', 'topNavBar.tpl');
    }

    private function addSignInOut()
    {
        $this->tpl['SIGNOUT_URL'] = UserStatus::getLogoutURI();
    }

    private function addTermSelector()
    {
        $termSelector = new TermSelector();
        $this->tpl['TERM_SELECTOR'] = $termSelector->show();
    }

    private function addSearch()
    {
        $this->tpl['STUDENT_SEARCH'] = '';
        javascript('jquery');
        javascriptMod('hms', 'studentSearch');
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
            $userTpl['FULL_NAME'] = '<strong class="text-danger">' . UserStatus::getDisplayName() . '</strong>';
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
                $this->tpl['DISPLAY_NAME'] = UserStatus::getUsername();
            } else {
                $this->tpl['DISPLAY_NAME'] = UserStatus::getDisplayName();
            }
        }
    }
}
