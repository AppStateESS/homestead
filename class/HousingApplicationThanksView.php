<?php

namespace Homestead;

//TODO update/convert this view

class HousingApplicationThanksView extends View {

    public function __construct()
    {

    }

    public function show()
    {
        $tpl = array();
        $tpl['VIEW_APPLICATION']    = PHPWS_Text::secureLink(_('View My Application'), 'hms', array('type'=>'student', 'op'=>'view_application'));
        $tpl['MAIN_MENU_LINK']      = PHPWS_Text::secureLink(_('Back to Main Menu'), 'hms', array('type'=>'student','op'=>'main'));
        $tpl['LOGOUT_LINK']         = PHPWS_Text::moduleLink(_('Logout'), 'users', array('action'=>'user', 'command'=>'logout'));

        // TODO HMS_Entry_Term is deprecated, use something else
        PHPWS_Core::initModClass('hms','HMS_Entry_Term.php');
        if(HMS_Entry_Term::get_entry_semester($_SESSION['asu_username']) == TERM_FALL){
            $tpl['RLC_LINK'] = PHPWS_Text::secureLink(_('Residential Learning Communities Application'), 'hms', array('type'=>'student', 'op'=>'show_rlc_application_form'));
        }

        Layout::addPageTitle("Thank you");

        return \PHPWS_Template::process($tpl, 'hms', 'student/student_application_thankyou.tpl');
    }
}
