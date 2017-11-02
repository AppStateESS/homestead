<?php

namespace Homestead;

/**
 * @author Jeremy Booker
 * @package Homestead
 */
class LeftNavBar extends View {

    private $tpl;

    public function __construct(){
        $this->tpl = array();
    }

    public function show()
    {
        $assignCmd = CommandFactory::getCommand('ShowAssignStudent');
        $this->tpl['ASSIGNMENTS_URI'] = $assignCmd->getUri();

        $hallCmd = CommandFactory::getCommand('SelectResidenceHall');
        $hallCmd->setTitle('Select Residence Hall');
        $hallCmd->setOnSelectCmd(CommandFactory::getCommand('EditResidenceHallView'));
        $this->tpl['HALLS_URI'] = $hallCmd->getUri();

        $communityCmd = CommandFactory::getCommand('ShowEditRlc');
        $this->tpl['RLC_URI'] = $communityCmd->getUri();

        $messageCmd = CommandFactory::getCommand('ShowHallNotificationSelect');
        $this->tpl['MESSAGING_URI'] = $messageCmd->getUri();

        $reportsCmd = CommandFactory::getCommand('ListReports');
        $this->tpl['REPORTS_URI'] = $reportsCmd->getUri();

        $this->tpl['SERVICE_DESK_URI'] = '';

        $this->tpl['REAPPLICATION_URI'] = '';

        if(\Current_User::allow('hms', 'edit_terms')) {
            $termCmd = CommandFactory::getCommand('ShowEditTerm');
        	$this->tpl['EDIT_TERM_URI'] = $termCmd->getURI();
        }

        if(\Current_User::allow('hms', 'view_activity_log')) {
            $termCmd = CommandFactory::getCommand('ShowActivityLog');
            $this->tpl['ACTIVITY_LOG_URI'] = $termCmd->getURI();
        }

    	if(\Current_User::isDeity()) {
            $ctrlPanel = CommandFactory::getCommand('ShowControlPanel');
            $this->tpl['CTRL_PANEL_URI'] = $ctrlPanel->getURI();

            $pulse = CommandFactory::getCommand('ShowPulseOption');
            $this->tpl['PULSE_URI'] = $pulse->getURI();
    	}

        return \PHPWS_Template::process($this->tpl, 'hms', 'leftNavBar.tpl');
    }
}
