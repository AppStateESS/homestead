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
		PHPWS_COre::initModClass('hms', 'CheckinFactory.php');
		PHPWS_COre::initModClass('hms', 'StudentFactory.php');
		PHPWS_COre::initModClass('hms', 'HousingApplicationFactory.php');
		PHPWS_COre::initModClass('hms', 'HMS_Assignment.php');
		
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
		
		$view = new InfoCardPdfView($student, $hall, $room, $application, $checkin);
		$pdf = $view->getPdf();
		$pdf->output();
		exit;
	}
}

?>