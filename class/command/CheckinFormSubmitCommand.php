<?php 

PHPWS_Core::initModClass('hms', 'StudentFactory.php');
PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');
PHPWS_Core::initModClass('hms', 'Checkin.php');
PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');

class CheckinFormSubmitCommand extends Command {

    private $bannerId;
    private $hallId;

    public function setBannerId($bannerId){
        $this->bannerId = $bannerId;
    }

    public function setHallId($hallId){
        $this->hallId = $hallId;
    }

    public function getRequestVars(){
        return array('action'	=> 'CheckinFormSubmit',
                     'bannerId'	=> $this->bannerId,
                     'hallId'   => $this->hallId);
    }

    public function execute(CommandContext $context)
    {
        $bannerId 	= $context->get('bannerId');
        $hallId		= $context->get('hallId');

        // Check for key code
        $keyCode = $context->get('key_code');

        if(!isset($keyCode) || $keyCode == ''){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'Please enter a key code.');
            $errorCmd = CommandFactory::getCommand('ShowCheckinForm');
            $errorCmd->setBannerId($bannerId);
            $errorCmd->setHallId($hallId);
            $errorCmd->redirect();
        }

        $term = Term::getCurrentTerm();

        // Lookup the student
        $student = StudentFactory::getStudentByBannerId($bannerId, $term);

        // Find the students housing app, or if none exists, create a new one
        $app = HousingApplicationFactory::getAppByStudent($student, $term);

        // TODO.. Separate the emergency contact info from the housing application
        if(!isset($app) || is_null($app)){
            $sem = Term::getTermSem($term);
            switch($sem){
                case TERM_FALL:
                    $app = new RestoredFallApplication();
                    $appType = 'fall';
                    $app->setLifestyleOption(0);
                    $app->setPreferredBedtime(0);
                    $app->setRoomCondition(0);
                    $app->setRlcPreference(0);
                    break;
                case TERM_SPRING:
                    $app = new RestoredSpringApplication();
                    $appType = 'spring';
                    $app->setLifestyleOption(0);
                    $app->setPreferredBedtime(0);
                    $app->setRoomCondition(0);
                    $app->setRlcPreference(0);
                    break;
                case TERM_SUMMER1:
                case TERM_SUMMER2:
                    $app = new RestoredSummerApplication();
                    $appType = 'summer';
                    break;
            }
            	
            // Setup the new application
            $app->setApplicationType($appType);
            $app->setTerm($term);
            $app->setBannerId($student->getBannerId());
            $app->setUsername($student->getUsername());
            $app->setGender($student->getGender());
            $app->setStudentType($student->getType());
            $app->setApplicationTerm($student->getApplicationTerm());
            $app->setCancelled(0);
            $app->setMealPlan(BANNER_MEAL_STD);
            $app->setInternational($student->isInternational());
        }

        // Update student's housing app
        $app->setCellPhone($context->get('cell_phone'));

        /* Emergency Contact */
        $app->setEmergencyContactName($context->get('emergency_contact_name'));
        $app->setEmergencyContactRelationship($context->get('emergency_contact_relationship'));
        $app->setEmergencyContactPhone($context->get('emergency_contact_phone'));
        $app->setEmergencyContactEmail($context->get('emergency_contact_email'));

        /* Missing Persons */
        $app->setMissingPersonName($context->get('missing_person_name'));
        $app->setMissingPersonRelationship($context->get('missing_person_relationship'));
        $app->setMissingPersonPhone($context->get('missing_person_phone'));
        $app->setMissingPersonEmail($context->get('missing_person_email'));

        /* Medical Conditions */
        $app->setEmergencyMedicalCondition($context->get('emergency_medical_condition'));

        $app->save();


        // Create the actual check-in and save it
        $assignment = HMS_Assignment::getAssignmentByBannerId($bannerId, $term);
        $bed		= $assignment->get_parent();

        $currUser = Current_User::getUsername();

        $checkin = new Checkin($student, $bed, $term, $currUser, $keyCode);

        $checkin->save();

        NQ::simple('hms', HMS_NOTIFICATION_SUCCESS, 'Checkin successful.');

        // Redirect to Checkin-PDF document
        $cmd = CommandFactory::getCommand('ShowCheckinDocument');
        $cmd->setBannerId($student->getBannerId());
        $cmd->setCheckinId($checkin->getId());

        $cmd->redirect();
    }

}
?>
