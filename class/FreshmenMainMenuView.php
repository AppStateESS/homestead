<?php

class FreshmenMainMenuView extends View {
	
	private $student;
	
	public function __construct(Student $student)
	{
		$this->student = $student;
	}
	
	public function show()
	{
        PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');
        $features = ApplicationFeature::getEnabledFeaturesForStudent($this->student, $this->student->getApplicationTerm());

        $output = "Main Menu:<br />\n";
        foreach($features as $feature) {
            $output .= $feature->getMenuBlockView($this->student) . "<br />\n";
        }
        return $output;
	}
	
}

?>
