<?php

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

        $infoCard = new InfoCard($checkin);

		$view = new InfoCardPdfView();
		$view->addInfoCard($infoCard);

        $view->getPdf()->output();
        exit;
    }
}

?>