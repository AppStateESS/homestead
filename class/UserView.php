<?php
namespace Homestead;

/**
 * HMS User View
 * All Non-Admin Authenticated Users.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

\PHPWS_Core::initModClass('hms', 'HomesteadView.php');

class UserView extends HomesteadView {
    
    public function show()
    {
        $tpl = array();
        $tpl['NOTIFICATIONS'] = $this->notifications;
        $tpl['MAIN'] = $this->getMain();

        $this->showHMS(\PHPWS_Template::process($tpl, 'hms', 'user.tpl'));
    }
}