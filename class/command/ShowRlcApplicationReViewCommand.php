<?php

class ShowRlcApplicationReViewCommand extends Command {

    private $username;
    
    public function setUsername($user){
        $this->username = $user;
    }
    
    public function getRequestVars(){
        $vars = array('action'=>'ShowRlcApplicationReView');
        
        $vars['username'] = $this->username;
        
        return $vars;
    }

    public function execute(CommandContext $context){
        
        // This is used both on the admin side and on the student side, so the permission check is a bit more complex
        if((UserStatus::isAdmin() && !Current_User::allow('view_rlc_applications')) || (UserStatus::isUser() && $context->get('username') != UserStatus::getUsername())){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to view this RLC application.');
        }
        
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        PHPWS_Core::initModClass('hms', 'RlcApplicationReView.php');

        try{
            $student = StudentFactory::getStudentByUsername($context->get('username'), Term::getSelectedTerm());
        }catch(StudentNotFoundException $e){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Unknown student.');
            $context->goBack();
        }

        $application = HMS_RLC_Application::getApplicationByUsername($student->getUsername(), $student->getApplicationTerm());

        $view = new RlcApplicationReView($student, $application);

        $context->setContent($view->show());
    }
}
?>