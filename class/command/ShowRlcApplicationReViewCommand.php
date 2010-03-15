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