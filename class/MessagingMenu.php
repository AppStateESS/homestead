<?php

PHPWS_Core::initModClass('hms', 'CommandMenu.php');

class MessagingMenu extends CommandMenu {
	
	public function __construct()
	{
		parent::__construct();
		
        $this->addCommandByName('Send messages by Hall', 'ShowHallNotificationSelect');
	}
	
	public function show()
	{
		$tpl = array();
		
		$tpl['MENU'] = parent::show();
		
		return PHPWS_Template::process($tpl, 'hms', 'admin/menus/MessagingMenu.tpl');
	}
}
