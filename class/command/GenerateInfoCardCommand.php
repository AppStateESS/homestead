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
		// TODO generate PDF
		test('PDF goes here',1);
	}
}

?>