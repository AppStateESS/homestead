<?php

namespace Homestead\Command;

use \Homestead\Term;
use \Homestead\StudentFactory;
use \Homestead\StudentProfile;
use \Homestead\NotificationView;
use \Homestead\Exception\PermissionException;
use \Homestead\Exception\StudentNotFoundException;

class ShowStudentProfileCommand extends Command {

    private $username;
    private $bannerId;

    public function setUsername($username){
        $this->username = $username;
    }

    public function setBannerId($id){
        $this->bannerId = $id;
    }

    public function getRequestVars(){
        $vars = array('action'=>'ShowStudentProfile');

        if(isset($this->username)){
            $vars['username'] = $this->username;
        }

        if(isset($this->bannerId)){
            $vars['bannerId'] = $this->bannerId;
        }

        return $vars;
    }

    public function execute(CommandContext $context)
    {
        if(!\Current_User::allow('hms', 'search')){
            throw new PermissionException('You do not have permission to search for students.');
        }

        $username = $context->get('username');
        $bannerId = $context->get('bannerId');
        $term = Term::getSelectedTerm();

        try{
            if(isset($bannerId)){
                // Sanity check banner id
                if(preg_match("/\A[\d]{9}\Z/", $bannerId) === 0){
                    // Invlid banner id
                    \NQ::simple('hms', NotificationView::ERROR, 'The Banner ID you entered is not formatted correctly. It must be nine digits.');
                    $context->goBack();

                }

                $student = StudentFactory::getStudentByBannerId($bannerId, $term);
            } else {
                $student = StudentFactory::getStudentByUsername($username, $term);
            }
        }catch (\InvalidArgumentException $e){
            \NQ::simple('hms', NotificationView::ERROR, $e->getMessage());
            /*
             $cmd = CommandFactory::getCommand('ShowStudentSearch');
            $cmd->setUsername($userid);
            $cmd->redirect();
            */
            $context->goBack();
        }catch (StudentNotFoundException $e){
            \NQ::simple('hms', NotificationView::ERROR, $e->getMessage());
            /*
             $cmd = CommandFactory::getCommand('ShowStudentSearch');
            $cmd->setUsername($userid);
            $cmd->redirect();
            */
            $context->goBack();
        }

        $profile = new StudentProfile($student, $term);

        $context->setContent($profile->getProfileView()->show());
    }
}
