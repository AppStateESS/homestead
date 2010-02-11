<?php

class CreateTermCommand extends Command {
	
	public function getRequestVars()
	{
		return array('action'=>'CreateTerm');
	}
	
	public function execute(CommandContext $context)
	{
		
	}
}

?>