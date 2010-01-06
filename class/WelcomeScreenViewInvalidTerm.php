<?php

PHPWS_Core::initModClass('hms', 'View.php');

class WelcomeScreenViewInvalidTerm extends View {
	
	public function show()
	{
		return PHPWS_Template::process($tpl, 'hms', 'student/welcome_screen_no_entry_term.tpl');
	}
}

?>