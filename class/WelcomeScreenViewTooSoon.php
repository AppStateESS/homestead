<?php

namespace Homestead;

class WelcomeScreenViewTooSoon extends View{

    public function show()
    {
        Layout::addPageTitle("Welcome");
        return PHPWS_Template::process($tpl, 'hms', 'student/welcome_screen_too_soon.tpl');
    }
}
