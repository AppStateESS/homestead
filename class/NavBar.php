<?php
namespace hms;

/**
 * NavBar.php
 *
 * @author jbooker
 * @package package_name
 */
class NavBar extends View {

    private $tpl;

    public function __construct()
    {
    	$this->tpl = array();
    }

    public function show()
    {
        $this->addSignInOut();

        $this->addUserStatus();

    	if(\UserStatus::isAdmin())
        {
        	$this->addTermSelector();
        }

        $this->addSearch();
        $this->addHallLink();
        $this->addReportLink();
        $this->addSettings();

        return \PHPWS_Template::process($this->tpl, 'hms', 'navbar.tpl');
    }

    private function addSignInOut()
    {
    	if(\UserStatus::isGuest())
        {
        	$this->tpl['SIGNIN_URL'] = './secure';
        } else {
        	$this->tpl['SIGNOUT_URL'] = \UserStatus::getLogoutURI();
        }
    }

    private function addUserStatus()
    {
        // If the user is not logged in, then we have nothing to do here
    	  if(\UserStatus::isGuest()){
    		    return;
    	  }

				$userTpl = array();

        $userTpl['FULL_NAME'] = \UserStatus::getDisplayName();
        $useDropdown = false;

        if (\UserStatus::isMasquerading() && \UserStatus::isMasqueradingAsSelf()) {
            // User is masquerading as student version of self
            $useDropdown = true;
            $userTpl['FULL_NAME'] = \UserStatus::getDisplayName() . ' (student)';
            $cmd = \CommandFactory::getCommand('RemoveMaskAsSelf');
            $userTpl['STUDENT_SELF_RETURN'] = $cmd->getURI(); // Link to return to admin version of self
        } else if (\UserStatus::isMasquerading()) {
            // User is masquerading as a student
            $useDropdown = true;
            $cmd = \CommandFactory::getCommand('RemoveMask');
            $userTpl['REMOVE_MASK'] = $cmd->getURI();
            $userTpl['FULL_NAME'] = '<strong class="text-danger">' . \UserStatus::getDisplayName() . '</strong>';
        } else if (\Current_User::allow('hms', 'ra_login_as_self')) {
            // User is not masquerading, but do have permission to change to student self-view
            $useDropdown = true;
            $studentViewCmd = \CommandFactory::getCommand('RaMasqueradeAsSelf');
            $userTpl['STUDENT_VIEW_URI'] = $studentViewCmd->getURI();
        }

        if($useDropdown){
            // Other options available, so we'll render a drop down
            $this->tpl['USER_STATUS_DROPDOWN'] = \PHPWS_Template::process($userTpl, 'hms', 'UserStatus.tpl');

        }else{
            // No other options, so the user status is just the display name
        	$this->tpl['DISPLAY_NAME'] = \UserStatus::getDisplayName();
        }
    }

    private function addTermSelector()
    {
    	\PHPWS_Core::initModClass('hms', 'TermSelector.php');
        $termSelector = new TermSelector();
        $this->tpl['TERM_SELECTOR'] = $termSelector->show();
    }

    private function addHallLink()
    {
    	if(\Current_User::allow('hms', 'hall_view')) {
            $residenceHallCmd = \CommandFactory::getCommand('SelectResidenceHall');
            $residenceHallCmd->setOnSelectCmd(\CommandFactory::getCommand('EditResidenceHallView'));
    		$this->tpl['HALL_VIEW'] = $residenceHallCmd->getURI();
    	}
    }

    private function addReportLink()
    {
    	if(\Current_User::allow('hms', 'reports')) {
            $cmd = \CommandFactory::getCommand('ListReports');
            $this->tpl['REPORT_LINK'] = $cmd->getURI();
        }
    }

    private function addSearch()
    {
    	if(\Current_User::allow('hms','search')) {
            $this->tpl['STUDENT_SEARCH'] = '';
            javascript('jquery');
            javascriptMod('hms', 'studentSearch');
        }
    }

    private function addSettings()
    {
        //$this->tpl['DROPDOWN'] = '';
        //$this->tpl['SETTINGS'][] = array('LINK' => $ctrlPanel->getLink('Control Panel'));

        if(\Current_User::allow('hms', 'edit_terms')) {
            $termCmd = \CommandFactory::getCommand('ShowEditTerm');
        	$this->tpl['EDIT_TERM_URI'] = $termCmd->getURI();
        }

        if(\Current_User::allow('hms', 'view_activity_log')) {
            $termCmd = \CommandFactory::getCommand('ShowActivityLog');
            $this->tpl['ACTIVITY_LOG_URI'] = $termCmd->getURI();
        }

    	if(\Current_User::isDeity()) {
            $ctrlPanel = \CommandFactory::getCommand('ShowControlPanel');
            $this->tpl['CTRL_PANEL_URI'] = $ctrlPanel->getURI();

            $pulse = \CommandFactory::getCommand('ShowPulseOption');
            $this->tpl['PULSE_URI'] = $pulse->getURI();
    	}
    }
}
