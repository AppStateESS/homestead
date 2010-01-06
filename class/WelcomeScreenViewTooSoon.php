<?php

PHPWS_Core::initModClass('hms', 'View.php');

class WelcomeScreenViewTooSoon extends View {
	
	public function show()
	{
		return PHPWS_Template::process($tpl, 'hms', 'student/welcome_screen_too_soon.tpl');
	}
}

?>