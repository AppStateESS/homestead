<?php

PHPWS_Core::initModClass('hms', 'CommandMenu.php');

class MessagingMenu extends CommandMenu {

    public function __construct()
    {
        parent::__construct();
    	//if(UserStatus::isAdmin() && (Current_User::allow('hms', 'email_hall') || Current_User::allow('hms', 'email_all'))){
            $this->addCommandByName('Send messages by Hall', 'ShowHallNotificationSelect');
        //}

        if(UserStatus::isAdmin() &&
        Current_User::allow('hms', 'assignment_notify')){
            $cmd = CommandFactory::getCommand('JSConfirm');
            $cmd->setLink('Send assignment notifications');
            $cmd->setTitle('Send assignment notification emails');
            $cmd->setQuestion('Send assignment notification emails for the selected term?');
            
            $cmd->setOnConfirmCommand(CommandFactory::getCommand('SendAssignmentNotification'));
            
            $this->addCommand('Send assignment notifications', $cmd);
        }
    }

    public function show()
    {
        if(empty($this->commands)){
            return "";
        }
        $tpl = array();

        $tpl['MENU'] = parent::show();

		return PHPWS_Template::process($tpl, 'hms', 'admin/menus/MessagingMenu.tpl');
	}
}
