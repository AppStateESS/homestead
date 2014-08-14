<?php

/**
 * HMS User View
 * All Non-Admin Authenticated Users.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class UserView extends hms\HMSView{

    public $notifications;

    public function addNotifications($n)
    {
        $this->notifications = $n;
    }

    public function show()
    {
        $tpl = array();
        $tpl['NOTIFICATIONS'] = $this->notifications;
        $tpl['MAIN'] = $this->getMain();

        $this->showHMS(PHPWS_Template::process($tpl, 'hms', 'user.tpl'));
    }
}

?>
