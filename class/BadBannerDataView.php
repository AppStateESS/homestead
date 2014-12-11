<?php

class BadBannerDataView extends homestead\View {

    public function __construct()
    {
    }

    public function show()
    {
        $tpl = array();

        $contactCmd = CommandFactory::getCommand('ShowContactForm');

        $tpl['CONTACT_LINK'] = $contactCmd->getLink('click here to contact us.');
        
        Layout::addPageTitle("Contact");

        return PHPWS_Template::process($tpl, 'hms', 'student/badBannerDataView.tpl');
    }
}

?>
