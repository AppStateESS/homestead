<?php

class ShowStudentSearchCommand extends Command {

    private $username;

    public function setUsername($username){
        $this->username = $username;
    }

    function getRequestVars(){
        $vars = array('action'=>'ShowStudentSearch');

        if(isset($this->username)){
            $vars['username'] = $this->username;
        }

        return $vars;
    }

    function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !Current_User::allow('hms', 'search')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to search for students.');
        }

        javascript('jquery');


        $cmd = CommandFactory::getCommand('StudentSearch');

        $form = new PHPWS_Form('student_search_form');
        $cmd->initForm($form);

        $form->setMethod('get');

        $username = $context->get('username');
        if(isset($username)){
            $form->addText('username', $username);
        }else{
            $form->addText('username');
        }

        javascript('/modules/hms/new_autosuggest', array('ELEMENT' => $form->getId('username')));
        Layout::addStyle('hms', 'css/autosuggest2.css');

        javascript('/modules/hms/autoFocus', array('ELEMENT' => $form->getId('username')));

        $form->setExtra('username', 'autocomplete="off" ');

        $form->addSubmit('submit_button', _('Submit'));

        $tpl = $form->getTemplate();

        Layout::addPageTitle("Student Search");

        $context->setContent(PHPWS_Template::process($tpl, 'hms', 'admin/get_single_username.tpl'));
    }
}
