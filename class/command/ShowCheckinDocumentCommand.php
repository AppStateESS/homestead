<?php

PHPWS_Core::initModClass('hms', 'CheckinFactory.php');

class ShowCheckinDocumentCommand extends Command {
	
	private $bannerId;
	private $checkinId;
	
	public function setBannerId($bannerId){
		$this->bannerId = $bannerId;
	}
	
	public function setCheckinId($checkinId){
		$this->checkinId = $checkinId;
	}
	
	public function getRequestVars(){
		return array('action'		=> 'ShowCheckinDocument',
				 	 'bannerId' 	=> $this->bannerId,
					  'checkinId'	=> $this->checkinId);
	}
	
	public function execute(CommandContext $context)
	{
		// Load the checkin object
		$bannerId = $context->get('bannerId');
		$term = Term::getCurrentTerm();
		
		$checkin = CheckinFactory::getCheckinByBannerId($bannerId, $term);
		
		if(!isset($checkin) || is_null($checkin)){
			NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'There was an error while looking up this checkin. Please contact ESS.');
			$errCmd = CommandFactory::getCommand('ShowAdminMainMenu');
			$errCmd->redirect();
		}
		
		PHPWS_Core::initModClass('hms', 'CheckinDocumentView.php');
		$view = new CheckinDocumentView($checkin);
		
		$context->setContent($view->show());
	}
}

?>