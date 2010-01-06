<?php

class ShowStudentSearchCommand extends Command {

    function getRequestVars(){
        return array('action'=>'ShowStudentSearch');
    }

    function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'search')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to search for students.');
        }

        javascript('jquery');
        javascript('/modules/hms/new_autosuggest');
        Layout::addStyle('hms', 'css/autosuggest2.css');

        $cmd = CommandFactory::getCommand('StudentSearch');

        $form = &new PHPWS_Form('student_search_form');
        $cmd->initForm($form);

        $form->setMethod('get');

        $form->addText('username');
        $form->setExtra('username', 'autocomplete="off" ');

        $form->addSubmit('submit_button', _('Submit'));

        $tpl = $form->getTemplate();

        $context->setContent(PHPWS_Template::process($tpl, 'hms', 'admin/get_single_username.tpl'));
    }
}
