<?php

PHPWS_Core::initModClass('hms', 'StudentProfile.php');

class ShowStudentProfileCommand extends Command {

    private $username;
    private $bannerId;

    public function setUsername($username){
        $this->username = $username;
    }

    public function setBannerId($id){
        $this->bannerId = $id;
    }

    function getRequestVars(){
        $vars = array('action'=>'ShowStudentProfile');

        if(isset($this->username)){
            $vars['username'] = $this->username;
        }

        if(isset($this->bannerId)){
            $vars['bannerId'] = $this->bannerId;
        }

        return $vars;
    }

    function execute(CommandContext $context)
    {
        if(!Current_User::allow('hms', 'search')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to search for students.');
        }

        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'StudentProfile.php');

        $username = $context->get('username');
        $bannerId = $context->get('bannerId');
        $term = Term::getSelectedTerm();

        try{
            if(isset($bannerId)){
                $student = StudentFactory::getStudentByBannerId($bannerId, $term);
            } else {
                $student = StudentFactory::getStudentByUsername($username, $term);
            }
        }catch (InvalidArgumentException $e){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, $e->getMessage());
            /*
             $cmd = CommandFactory::getCommand('ShowStudentSearch');
            $cmd->setUsername($userid);
            $cmd->redirect();
            */
            $context->goBack();
        }catch (StudentNotFoundException $e){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, $e->getMessage());
            /*
             $cmd = CommandFactory::getCommand('ShowStudentSearch');
            $cmd->setUsername($userid);
            $cmd->redirect();
            */
            $context->goBack();
        }

        // Add the student object to the list of recent searches
        PHPWS_Core::initModClass('hms', 'RecentStudentSearchList.php');
        $searchList = RecentStudentSearchList::getInstance();
        $searchList->add($student, $term);

        $profile = new StudentProfile($student, $term);

        $context->setContent($profile->getProfileView()->show());
    }
}
