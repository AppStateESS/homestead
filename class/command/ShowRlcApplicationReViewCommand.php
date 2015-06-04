<?php

/**
 * Command to show the view for an existing RLC application.
 * 
 * @author jbooker
 * @package HMS
 */
class ShowRlcApplicationReViewCommand extends Command {

    private $appId;

    public function setAppId($id){
        $this->appId = $id;
    }

    public function getRequestVars(){
        $vars = array('action'=>'ShowRlcApplicationReView');

        $vars['appId'] = $this->appId;

        return $vars;
    }

    public function execute(CommandContext $context){

        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_RLC_Application.php');
        PHPWS_Core::initModClass('hms', 'RlcApplicationReView.php');

        $application = new HMS_RLC_Application($context->get('appId'));

        if(is_null($application->username)){
            NQ::simple('hms', hms\NotificationView::ERROR, 'There is no RLC application available with that id.');
            $context->goBack();
        }

        // This is used both on the admin side and on the student side, so the permission check is a bit more complex
        if((UserStatus::isAdmin() && !Current_User::allow('view_rlc_applications')) || (UserStatus::isUser() && $application->getUsername() != UserStatus::getUsername())){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to view this RLC application.');
        }

        try{
            $student = StudentFactory::getStudentByUsername($application->username, $application->term);
        }catch(StudentNotFoundException $e){
            NQ::simple('hms', hms\NotificationView::ERROR, 'Unknown student.');
            $context->goBack();
        }

        $view = new RlcApplicationReView($student, $application);

        $context->setContent($view->show());
    }
}
