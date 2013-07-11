<?php 

require_once PHPWS_SOURCE_DIR . 'mod/hms/pdf/fpdf.php';
require_once PHPWS_SOURCE_DIR . 'mod/hms/pdf/fpdi.php';

class GenerateInfoCardCommand extends Command {

    private $checkinId;

    public function setCheckinId($checkinID)
    {
        $this->checkinId = $checkinID;
    }

    public function getRequestVars()
    {
        return array('action' 	 => 'GenerateInfoCard',
                     'checkinId' => $this->checkinId);
    }

    public function execute(CommandContext $context)
    {

        PHPWS_Core::initModClass('hms', 'InfoCardPdfView.php');
        PHPWS_Core::initModClass('hms', 'CheckinFactory.php');
        PHPWS_Core::initModClass('hms', 'StudentFactory.php');
        PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');
        PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
        PHPWS_Core::initModClass('hms', 'RoomDamageFactory.php');

        $checkinId = $context->get('checkinId');

        $checkin = CheckinFactory::getCheckinById($checkinId);

        $bannerId = $checkin->getBannerId();
        $term = $checkin->getTerm();
        
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
        
        $fpdf = new FPDF('L', 'mm', 'Letter');
        
        $view = new InfoCardPdfView($fpdf, $student, $hall, $room, $application, $checkin, $damages);
        $pdf = $view->getPdf();
        $pdf->output();
        exit;
    }
}

?>