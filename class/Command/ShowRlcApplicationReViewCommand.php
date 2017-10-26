<?php

namespace Homestead\Command;

 use \Homestead\HMS_RLC_Application;
 use \Homestead\UserStatus;
 use \Homestead\NotificationView;
 use \Homestead\StudentFactory;
 use \Homestead\RlcApplicationReView;
 use \Homestead\Exception\PermissionException;
 use \Homestead\Exception\StudentNotFoundException;

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
        $application = new HMS_RLC_Application($context->get('appId'));

        if(is_null($application->username)){
            \NQ::simple('hms', NotificationView::ERROR, 'There is no RLC application available with that id.');
            $context->goBack();
        }

        // This is used both on the admin side and on the student side, so the permission check is a bit more complex
        if((UserStatus::isAdmin() && !\Current_User::allow('view_rlc_applications')) || (UserStatus::isUser() && $application->getUsername() != UserStatus::getUsername())){
            throw new PermissionException('You do not have permission to view this RLC application.');
        }

        try{
            $student = StudentFactory::getStudentByUsername($application->username, $application->term);
        }catch(StudentNotFoundException $e){
            \NQ::simple('hms', NotificationView::ERROR, 'Unknown student.');
            $context->goBack();
        }

        $view = new RlcApplicationReView($student, $application);

        $context->setContent($view->show());
    }
}
