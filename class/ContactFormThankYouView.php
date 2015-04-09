<?php

PHPWS_Core::initModClass('hms', 'View.php');

class ContactFormThankYouView extends Homestead\View{

    public function show(){
        $tpl = array();
        $tpl['LOGOUT_LINK'] = PHPWS_Text::secureLink(_('Log Out'), 'users', array('action'=>'user', 'command'=>'logout'));

        Layout::addPageTitle("Contact");

        return PHPWS_Template::process($tpl, 'hms', 'student/contact_form_thankyou.tpl');
    }
}
?>
