<?php

PHPWS_Core::initModClass('hms', 'View.php');

class TermMenu extends Homestead\View{

    public function __construct()
    {

    }

    public function show(){
        $tpl = array();

        $tpl['FORM'] = Term::getTermSelector();

        return PHPWS_Template::process($tpl, 'hms', 'admin/TermMenu.tpl');
    }
}

?>