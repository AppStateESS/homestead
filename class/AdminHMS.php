<?php

PHPWS_Core::initModClass('hms', 'HMS.php');

class AdminHMS extends HMS
{
	public function process()
	{
		$this->context->setDefault('action', 'ShowAdminMaintenanceMenu');
		parent::process();

		PHPWS_Core::initModClass('hms', 'UserView.php');
		$view = new UserView();
		$view->setMain($this->context->getContent());

		PHPWS_Core::initModClass('hms', 'TermMenu.php');
		$termMenu = new TermMenu();
		$view->addToSidebar($termMenu->show());

		PHPWS_Core::initModClass('hms', 'AdminMenu.php');
		$menu = new AdminMenu();
		$menu->setContext($this->context);
		$view->addToSidebar($menu->show());

		PHPWS_Core::initModClass('hms', 'HMSNotificationView.php');
		$nv = new HMSNotificationView();
		$nv->popNotifications();
		$view->addNotifications($nv->show());

		$view->show();

		$this->saveState();
	}
}

?>
