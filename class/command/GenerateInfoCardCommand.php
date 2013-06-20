<?php 

class GenerateInfoCardCommand extends Command {

    private $bannerId;

    public function setBannerId($bannerId){
        $this->bannerId = $bannerId;
    }

    public function getRequestVars(){
        return array('action' 	=> 'GenerateInfoCard',
                'bannerId'	=> $this->bannerId);
    }

    public function execute(CommandContext $context)
    {

        PHPWS_Core::initModClass('hms', 'InfoCardPdfView.php');
        PHPWS_Core::initModClass('hms', 'CheckinFactory.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'RoomDamageFactory.php');

        $bannerId = $context->get('bannerId');
        $term = Term::getCurrentTerm();

        $checkin = CheckinFactory::getCheckinByBannerId($bannerId, $term);

        $student = StudentFactory::getStudentByBannerId($bannerId, $term);
        $assignment = HMS_Assignment::getAssignmentByBannerId($bannerId, $term);
        $application = HousingApplicationFactory::getAppByStudent($student, $term);

        $bed = $assignment->get_parent();
        $room = $bed->get_parent();
        $floor = $room->get_parent();
        $hall = $floor->get_parent();
        
        $damages = RoomDamageFactory::getDamagesByRoom($room);
        if(!isset($damages) || is_null($damages)){
            $damages = array();
        }
        
        $view = new InfoCardPdfView($student, $hall, $room, $application, $checkin, $damages);
        $pdf = $view->getPdf();
        $pdf->output();
        exit;
    }
}

?>