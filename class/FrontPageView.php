<?php

class FrontPageView extends hms\View{

    public function show()
    {
        $tpl = array();
        $tpl['LOGIN_LINK'] = ''; // a dummy tag to make the actual login content show

        return PHPWS_Template::process($tpl, 'hms', 'misc/login.tpl');
    }
}

?>
