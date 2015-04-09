<?php

namespace Homestead;

/**
 * HMS Guest View
 * Shows them a friendly message and then mostly the login page
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class GuestView extends hms\HMSView{

    private $message;

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function show()
    {
        $tpl = array();
        $tpl['MAIN'] = $this->getMain();
        $tpl['MESSAGE'] = $this->getMessage();
        $tpl['NOTIFICATIONS'] = $this->notifications;

        \Layout::addPageTitle("Login");

        $this->showHMS(\PHPWS_Template::process($tpl, 'hms', 'guest.tpl'));
    }
<<<<<<< HEAD
}

?>
=======
}
>>>>>>> Rename namespace to 'Homestead' instead of 'hms'.
