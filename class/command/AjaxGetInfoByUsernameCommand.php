<?php

PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'Term.php');
class AjaxGetInfoByUsernameCommand extends Command {

    public function getRequestVars(){
        return array();
    }

    public function execute(CommandContext $context){
        try {
            $username = $context->get('username');

            $currUser = UserStatus::getUsername();

            $term = Term::getSelectedTerm();

            $currentUser = StudentFactory::getStudentByUsername($currUser, $term);
            $assignment = HMS_Assignment::getAssignment($username, $term);


            try
            {
              $student = StudentFactory::getStudentByUsername($username, Term::getSelectedTerm());
            }
            catch(Exception $e)
            {
              $nameInfo = array('username' => $username, 'name' => 'Invalid User');
            }

            if($student->getGender() != $currentUser->getGender())
            {
              $nameInfo = array('username' => $username, 'name' => 'Invalid User');
            }
            else
            {
              $nameInfo = array('username' => $username, 'name' => $student->getName(), 'currentBedId' => $assignment->getBedId());
            }
        } catch(Exception $e) { //For display issues we really do want to catch any exception
            echo '<div style="display: none;">'.$e->getMessage().'</div>';
        }
        $context->setContent(json_encode($nameInfo));
    }
}
