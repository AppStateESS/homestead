<?php

PHPWS_Core::initModClass('hms', 'Command.php');

class AjaxGetBedByUsernameCommand extends Command {

    private $floorId;

    public function getRequestVars(){
        return array('action'=>'AjaxGetBedByUsername');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        $username = $context->get('username');

        $term = Term::getSelectedTerm();

        // Make sure a student exists for the given username
        try {
          $student = StudentFactory::getStudentByUsername($username, $term);
        } catch(StudentNotFoundException $e){
          // The student did not exist so an error will be thrown back to the front end
          // with the username of the student, most likely this has happened because the
          // values were passed without using the web page, as the front end keeps an invalid user
          // from being picked.
          $returnMsg = array('error' => 'invalid_user', 'message' => 'A student with the username '. $username . ' does not exist in our records.');
          header("HTTP/1.1 500 Internal Server Error");
          echo json_encode($returnMsg);
          exit;
        }

        // Get the assignment for the given username
        $assignment = HMS_Assignment::getAssignment($username, $term);

        // Check to make sure the assignmment is valid
        if (is_null($assignment)) {
          // This student is not currently assigned and an error will be thrown back to the front end
          // with the username of the student who is not assigned.
          $returnMsg = array('error' => 'unassigned_user', 'message' => 'The student with username ' . $username.' does not appear to be assigned at present, so cannot participate in a room change.');
          header("HTTP/1.1 500 Internal Server Error");
          echo json_encode($returnMsg);
          exit;
        }

        // Retrieve the bed for this assignment
        $bed = BedFactory::getBedByBedId($assignment->getBedId(), $term);

        // Get the bed's id and location.
        $nameInfo = array('bedId' => $bed->getId(), 'location' => $bed->where_am_i());

        // Encode to json and return it to the front end
        $context->setContent(json_encode($nameInfo));
    }
}
