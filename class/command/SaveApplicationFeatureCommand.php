<?php

class SaveApplicationFeatureCommand extends Command {
	
	private $featureId;
	private $term;
	private $name;
	
	public function getRequestVars()
	{
		$vars = array('action'=>'SaveApplicationFeature');
		
		if(isset($this->featureId))
		{
			$vars['featureId'] = $this->featureId;
		}
		
		if(isset($this->term)) {
			$vars['term'] = $this->term;
		}
		
		if(isset($this->name)) {
			$vars['name'] = $this->name;
		}
		
		return $vars;
	}
	
	public function setFeatureId($id) {
		$this->featureId = $id;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function setTerm($term) {
		$this->term = $term;
	}
	
	public function execute(CommandContext $context)
	{
		if(!Current_User::allow('hms', 'deadlines')) {
			PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
			throw new PermissionException('You do not have permission to edit deadlines.');
		}
		
		if(!isset($this->featureId)) {
			$this->featureId = $context->get('featureId');
		}
		$featureId = $this->featureId;
        
        if(!isset($this->term)) {
            $this->term = $context->get('term');
        }
        $term = $this->term;
        
        if(!isset($this->name)) {
            $this->name = $context->get('name');
        }
        $name = $this->name;
        
        PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');
        if(!is_null($featureId)) {
        	$feature = ApplicationFeature::getInstanceById($featureId);
        } else if(!is_null($name) && !is_null($term)) {
        	$feature = ApplicationFeature::getInstanceByNameAndTerm($name, $term);
        } else {
        	throw new InvalidArgumentException('You must either provide a featureId, or a name and a term.');
        }
        
        // Checkboxes are weird.
        $enabled = !is_null($context->get('enabled'));
        
        $feature->setEnabled($enabled);
        if($enabled) {
        	$startDate = strtotime($context->get('start_date'));
        	$endDate   = strtotime($context->get('end_date'));
        	
        	if(!is_null($startDate)) {
        		$feature->setStartDate($startDate);
        	}
        	
        	if(!is_null($endDate)) {
        		$feature->setEndDate($endDate);
        	}
        }
        
        $feature->save();
        
        echo "Success!";
        HMS::quit();
	} 
}