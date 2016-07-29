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

            // Retrieve the username of the current user
            $currUser = UserStatus::getUsername();

            $term = Term::getSelectedTerm();

            // Get the current user's gender in order to ensure that all user's in the
            // room change will be of the same gender.
            $currentUser = StudentFactory::getStudentByUsername($currUser, $term);
            $initGender = $currentUser->getGender();

            // Ensure that the username provided gives an actual student object.
            try {
              $student = StudentFactory::getStudentByUsername($username, $term);
            } catch(StudentNotFoundException $e){
              // The student did not exist so an error will be thrown back to the front end
              // with the username of the student, most likely this has happened because the
              // values were passed without using the web page, as the front end keeps an invalid user
              // from being picked.
              $errorInfo = array('error' => 'invalid_user', 'message' => 'A student with the username '. $username . ' does not exist in our records.');
              $returnMsg = array('status' => 'error', 'error' => $errorInfo);
              header("HTTP/1.1 500 Internal Server Error");
              echo json_encode($returnMsg);
              exit;
            }

            // Retrieve the assignment for the username given
            $assignment = HMS_Assignment::getAssignment($username, $term);

            if($student->getGender() != $initGender)
            {
              // This student is not the same gender as the student who initialized this room change.
              // At this point coed rooms are not allowed.
              $errorInfo = array('error' => 'invalid_gender', 'message' => 'Error occurred. Please ensure that all participants are of the same sex.');
              $returnMsg = array('status' => 'error', 'error' => $errorInfo);
              header("HTTP/1.1 500 Internal Server Error");
              echo json_encode($returnMsg);
              exit;
            }

            // Sets the values of the username and the relevant info into an array
            // so they can be returned as a json encoded string.
            $nameInfo = array('username' => $username, 'name' => $student->getName(), 'currentBedId' => $assignment->getBedId());

            $context->setContent(json_encode($nameInfo));
        } catch(Exception $e) { //For display issues we really do want to catch any exception
            echo '<div style="display: none;">'.$e->getMessage().'</div>';
        }
    }
}
