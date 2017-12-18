<?php

namespace Homestead;

/**
 * HMS User View
 * All Non-Admin Authenticated Users.
 * @author Jeremy Booker
 * @package Homestead
 */

class AdminView extends HomesteadView {

    public function render()
    {
        $tpl = array();
        $tpl['NOTIFICATIONS'] = $this->notifications;
        $tpl['MAIN'] = $this->getMain();

        // Top nav bar
        $topNav = new TopNavBar();
        $tpl['TOP_NAV'] = $topNav->show();

        $leftNav = new LeftNavBar();
        $tpl['LEFT_NAV'] = $leftNav->show();

        \Layout::addStyle('hms', 'css/hms.css');

        \Layout::add(\PHPWS_Template::process($tpl, 'hms', 'adminView.tpl'));
    }
}
