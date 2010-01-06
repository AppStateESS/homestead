<?php

PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');

class StudentMenuTermBlock {
	
	private $student;
	private $term;
	
	public function __construct(Student $student, $term)
	{
		$this->student  = $student;
		$this->term		= $term;
	}
	
	public function show()
	{
		// Get the enabled features
		$features = ApplicationFeature::getEnabledFeaturesForStudent($this->student, $this->term);
		
		$tpl = array();
		$tpl['TERM'] = Term::toString($this->term);
		
		foreach($features as $feat){
			$tpl['BLOCKS'][] = array('BLOCK'=>$feat->getMenuBlockView($this->student)->show());
		}
		
		return PHPWS_Template::process($tpl, 'hms', 'student/studentMenuTermBlock.tpl');
	}
}

?>
