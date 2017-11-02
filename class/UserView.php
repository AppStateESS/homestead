<?php

namespace Homestead;

/**
 * HMS User View
 * All Non-Admin Authenticated Users.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 * @package Homestead
 */

class UserView extends HomesteadView {

    public function render()
    {
        $tpl = array();
        $tpl['NOTIFICATIONS'] = $this->notifications;
        $tpl['MAIN'] = $this->getMain();

        // Top nav bar
        $navBar = new StudentNavBar();
        $tpl['NAV_BAR'] = $navBar->show();

        \Layout::addStyle('hms', 'css/hms.css');

        \Layout::add(\PHPWS_Template::process($tpl, 'hms', 'user.tpl'));
    }
}
