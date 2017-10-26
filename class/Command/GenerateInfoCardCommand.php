<?php

namespace Homestead\Command;

 use \Homestead\InfoCard;
 use \Homestead\InfoCardPdfView;
 use \Homestead\CheckinFactory;

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
        $checkinId = $context->get('checkinId');

        $checkin = CheckinFactory::getCheckinById($checkinId);

        $infoCard = new InfoCard($checkin);

		$view = new InfoCardPdfView();
		$view->addInfoCard($infoCard);

        $view->getPdf()->output();
        exit;
    }
}
