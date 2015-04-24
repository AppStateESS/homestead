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
        
        $this->addUserFullName();
        
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
    
    private function addUserFullName()
    {
    	if(\UserStatus::isGuest()){
    		return;
    	}
        
        $this->tpl['FULL_NAME'] = \UserStatus::getDisplayName();
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
            //TODO - Expand this into an actual working search box
        }
    }
    
    private function addSettings()
    {
        $this->tpl['DROPDOWN'] = '';
        //$this->tpl['SETTINGS'][] = array('LINK' => $ctrlPanel->getLink('Control Panel'));
        
        if(\Current_User::allow('hms', 'edit_terms')) {
            $termCmd = \CommandFactory::getCommand('ShowEditTerm');
        	$this->tpl['EDIT_TERM_URI'] = $termCmd->getURI();
        }
        
        if(\Current_User::allow('hms', 'view_activity_log')) {
            $termCmd = \CommandFactory::getCommand('ShowActivityLog');
            $this->tpl['ACTIVITY_LOG_URI'] = $termCmd->getURI();
        }
        
        if (\Current_User::allow('hms', 'ra_login_as_self')) {
        	$studentViewCmd = \CommandFactory::getCommand('RaMasqueradeAsSelf');
            $this->tpl['STUDENT_VIEW_URI'] = $studentViewCmd->getURI();
        }
        
    	if(\Current_User::isDeity()) {
            $ctrlPanel = \CommandFactory::getCommand('ShowControlPanel');
            $this->tpl['CTRL_PANEL_URI'] = $ctrlPanel->getURI();
    	}
    }
}
?>
