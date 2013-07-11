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
        PHPWS_Core::initModClass('hms', 'InfoCard.php');
        PHPWS_Core::initModClass('hms', 'CheckinFactory.php');

        $checkinId = $context->get('checkinId');

        $checkin = CheckinFactory::getCheckinById($checkinId);

        $pdf = new FPDF('L', 'mm', 'Letter');
        
        $infoCard = new InfoCard($checkin);
			
		$view = new InfoCardPdfView($pdf, $infoCard);
		$view->addInfoCard();
		
        $pdf->output();
        exit;
    }
}

?>