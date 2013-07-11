<?php 

require_once PHPWS_SOURCE_DIR . 'mod/hms/pdf/fpdf.php';
require_once PHPWS_SOURCE_DIR . 'mod/hms/pdf/fpdi.php';

/**
 * Controller/command for generating the entire set of RIC forms for a semester.
 * 
 * @author jbooker
 * @package hms
 * @see GenerateInfoCardCommand
 */
class GenerateAllInfoCardsCommand extends Command {
	
	public function getRequestVars()
	{
		return array('action' => 'GenerateAllInfoCards');
	}
	
	public function execute(CommandContext $context)
	{
		PHPWS_Core::initModClass('hms', 'CheckinFactory.php');
		PHPWS_Core::initModClass('hms', 'InfoCard.php');
		PHPWS_Core::initModClass('hms', 'InfoCardPdfView.php');
		
		$term = Term::getSelectedTerm();
		
		$checkins = CheckinFactory::getCheckinsOrderedByRoom($term);
		
		$pdf = new FPDF('L', 'mm', 'Letter');
		
		foreach($checkins as $checkin) {
			$infoCard = new InfoCard($checkin);
			
			$view = new InfoCardPdfView($pdf, $infoCard);
			$view->addInfoCard();
		}
		
		$pdf->output();
		exit;
	}
}

?>