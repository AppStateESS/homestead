<?php

class ShowStudentSearchCommand extends Command {

    function getRequestVars(){
        return array('action'=>'ShowStudentSearch');
    }

    function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'search')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to search for students.');
        }

        javascript('jquery');
        javascript('jquery_ui');

        javascriptMod('hms', 'appCardSwipe');
        javascriptMod('hms', 'fuzzyAutocomplete');

        $cmd = CommandFactory::getCommand('StudentSearch');

        $form = new PHPWS_Form('student_search_form');
        $cmd->initForm($form);

        $form->setMethod('get');

        $form->addText('banner_id');
        $form->setExtra('banner_id', 'placeholder = "Swipe AppCard or type Name/Email/Banner ID" autofocus');
        $form->setClass('banner_id', 'checkin-search-box');

        $form->addSubmit('Search');
        $form->setClass('submit', 'btn btn-primary');
        
        $form->setProtected(false);

        $tpl = $form->getTemplate();

        Layout::addPageTitle("Student Search");

        $context->setContent(PHPWS_Template::process($tpl, 'hms', 'admin/student_search.tpl'));
    }
}
?>
