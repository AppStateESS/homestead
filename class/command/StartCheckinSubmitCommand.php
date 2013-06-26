<?php 

class StartCheckinSubmitCommand extends Command {

    public function getRequestVars()
    {
        return array('action'=>'StartCheckinSubmit');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');

        $term = Term::getCurrentTerm();

        $bannerId = $context->get('banner_id');
        $hallId   = $context->get('residence_hall_hidden');


        $errorCmd = CommandFactory::getCommand('ShowCheckinStart');

        if(!isset($bannerId) || is_null($bannerId) || $bannerId == ''){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Missing Banner ID.');
            $errorCmd->redirect();
        }

        if(!isset($hallId)){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Missing residence hall ID.');
            $errorCmd->redirect();
        }
        
        if(preg_match("/^[\d]{9}$/", $bannerId) == false){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, "Sorry, that didn't look like a valid ID number. Please try again.");
            $errorCmd->redirect();
        }

        // Try to lookup the student in Banner
        try {
            $student = StudentFactory::getStudentByBannerId($bannerId, $term);
        }catch(StudentNotFoundException $e){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Could not locate a student with that Banner ID.');
            $errorCmd->redirect();
        }

        // Everything checks out, so redirect to the form
        $cmd = CommandFactory::getCommand('ShowCheckinForm');
        $cmd->setBannerId($bannerId);
        $cmd->setHallId($hallId);
        $cmd->redirect();
    }
}

?>
