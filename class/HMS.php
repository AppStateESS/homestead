<?php

/**
 * Primary HMS class
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('hms', 'Term.php');
PHPWS_Core::initModClass('hms', 'UserStatus.php');
PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'CommandContext.php');
PHPWS_Core::initModClass('hms', 'CommandFactory.php');
PHPWS_Core::initModClass('hms', 'HMSNotificationView.php');

abstract class HMS {
	var $context;

	public function __construct()
	{
		$this->context = new CommandContext();
	}

	public function getContext()
	{
		return $this->context;
	}

	public function process()
	{
		// This hack is the most awful hack ever.  Fix phpWebSite so that
		// user logins are logged separately.
    	if(Current_User::isLogged() && !isset($_SESSION['HMS_LOGGED_THE_LOGIN'])) {
            PHPWS_Core::initModClass('hms','HMS_Activity_Log.php');
            $username = strtolower(Current_User::getUsername());
            HMS_Activity_Log::log_activity($username,ACTIVITY_LOGIN, $username, NULL);
            $_SESSION['HMS_LOGGED_THE_LOGIN'] = $username;
        }
		
        if(!Current_User::isLogged() && $this->context->get('action') != 'ShowFrontPage'){
            NQ::simple('hms', HMS_NOTIFICATION_ERROR, 'You must be logged in to do that.');
            $action = 'ShowFrontPage';
        }else{
            $action = $this->context->get('action'); 
        }
        
		$cmd = CommandFactory::getCommand($action);
		
		try {
			$cmd->execute($this->context);
		} catch(Exception $e) {
			try {
				$message = $this->formatException($e);
				NQ::Simple('hms', HMS_NOTIFICATION_ERROR, 'An internal error has occurred, and the authorities have been notified.  We apologize for the inconvenience.');
				self::emailError($message);
				PHPWS_Core::initModClass('hms', 'HMSNotificationView.php');
				$nv = new HMSNotificationView();
				$nv->popNotifications();
				Layout::add($nv->show());
			} catch(Exception $e) {
				$message2 = $this->formatException($e);
				echo "HMS has experienced a major internal error.  Attempting to email an admin and then exit.";
				$message = "Something terrible has happened, and the exception catch-all threw an exception.\n\nThe first exception was:\n\n$message\n\nThe second exception was:\n\n$message2";
				mail('webmaster@tux.appstate.edu', 'A Major HMS Error Has Occurred', $message);
				exit();
			}
		}
	}

	private function formatException(Exception $e)
	{
		ob_start();
		echo "Ohes Noes!  HMS threw an exception that was not caught!\n\n";
		echo "Here is CurrentUser:\n\n";
		print_r(Current_User::getUserObj());
		echo "\n\nHere is the exception:\n\n";
		print_r($e);
		echo "\n\nHere is the CommandContext:\n\n";
		print_r($this->context);
		echo "\n\nHere is $_REQUEST:\n\n";
		print_r($_REQUEST);
		$message = ob_get_contents();
		ob_end_clean();

		return $message;
	}

	private static function emailError($message)
	{
		PHPWS_Core::initModClass('hms', 'HMS_Email.php');
		//$to = HMSSettings::getUberAdminEmail();
		$to = HMS_Email::get_tech_contacts();
		
		$tags = array('MESSAGE' => $message);
		HMS_Email::send_template_message($to, 'Uncaught Exception',
		    'email/UncaughtException.tpl', $tags);
	}

	protected function saveState()
	{
		$this->context->saveLastContext();
	}

	public function getView()
	{
		$this->view->render();
	}

	public static function quit()
	{
		NQ::close();
		exit();
	}
}

?>
