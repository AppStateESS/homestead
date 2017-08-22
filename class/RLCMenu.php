<?php

namespace Homestead;

class RLCMenu extends CommandMenu {

    public function __construct()
    {
        parent::__construct();

        // Check permissions
        if(UserStatus::isAdmin()){

            if(Current_User::allow('hms', 'learning_community_maintenance')){
                $this->addCommandByName('Add/Edit Communities', 'ShowEditRlc');
            }

            if(Current_User::allow('hms', 'view_rlc_applications')){
                $this->addCommandByName('Assign Applicants to RLCs', 'ShowAssignRlcApplicants');
                $this->addCommandByName('View Denied Applications', 'ShowDeniedRlcApplicants');
            }

            if(Current_User::allow('hms', 'learning_community_maintenance')){
                $this->addCommandByName('Send RLC Email Invites', 'ShowSendRlcInvites');
            }

            if(Current_User::allow('hms' ,'view_rlc_members')){
                $this->addCommandByName('View RLC Members by RLC', 'ShowSearchByRlc');
                $this->addCommandByName('View RLC Assignments', 'ViewRlcAssignments');
            }
            if(Current_User::allow('hms', 'email_rlc_rejections')){
                // Using JSConfirm, ask user if the _really_ want to send the emails
                $onConfirmCmd = CommandFactory::getCommand('SendRlcRejectionEmails');
                $cmd = CommandFactory::getCommand('JSConfirm');

                $cmd->setLink('Send RLC Rejection Emails');
                $cmd->setTitle('Send RLC Rejection Emails');
                $cmd->setQuestion('Send notification emails to denied RLC applicants for selected term?');
                $cmd->setOnConfirmCommand($onConfirmCmd);
                $this->addCommand('Send RLC Rejection Emails', $cmd);
            }
        }
    }

    public function show()
    {
        if(empty($this->commands)){
            return "";
        }

        $tpl = array();

        $tpl['MENU'] = parent::show();

        return \PHPWS_Template::process($tpl, 'hms', 'admin/menus/RLCMenu.tpl');
    }
}
