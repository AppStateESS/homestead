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
		$cmd = CommandFactory::getCommand($this->context->get('action'));
		
		try {
			$cmd->execute($this->context);
		} catch (UnsupportedFunctionException $e){
			NQ::Simple('hms', HMS_NOTIFICATION_ERROR, 'Unsupported Function Exception: ' . $e->getMessage());
		} 
		
		/*
		catch(Exception $e) {
			try {
				$message = $this->formatException($e);
				NQ::Simple('hms', HMS_NOTIFICATION_ERROR, 'An internal error has occurred, and the authorities have been notified.  We apologize for the inconvenience.');
				self::emailError($message);
				CommandContext::goBack();
			} catch(Exception $e) {
				$message2 = $this->formatException($e);
				echo "HMS has experienced a major internal error.  Attempting to email an admin and then exit.";
				$message = "Something terrible has happened, and the exception catch-all threw an exception.\n\nThe first exception was:\n\n$message\n\nThe second exception was:\n\n$message2";
				mail('webmaster@tux.appstate.edu', 'A Major HMS Error Has Occurred', $message);
				exit();
			}
		}
		*/
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
		/*
		PHPWS_Core::initModClass('hms', 'EmailMessage.php');
		$to = HMSSettings::getUberAdminEmail();
		$to = 'jbooker@tux.appstate.edu';

		if(is_null($to) || empty($to)) {
			throw new Exception('No Uber Admin Email was set.  Please check HMS Global Settings.');
		}
		$email = new EmailMessage($to, 'hms_system', $to, NULL, NULL, NULL, 'Uncaught Exception', 'email/admin/UncaughtException.tpl');

		$email_tags = array('MESSAGE' => $message);

		$email->setTags($email_tags);
		$email->send();
		*/
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

/**
 class HMS
 {
 public function main($type = NULL)
 {

 if(!Current_User::isLogged()) {
 $error = "<i><font color=red>Please enter a valid username/password pair.</font></i>";
 PHPWS_Core::initModClass('hms', 'HMS_Login.php');
 HMS_Login::display_login_screen($error);
 } else {
 $username = Current_User::getUsername();
 require_once(PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php');
 if( isset($_REQUEST['login_as_student']) || isset($_SESSION['login_as_student']) ) {
 if( $type == ADMIN || Current_User::allow('hms', 'login_as_student') ) {
 if( isset($_REQUEST['login_as_student']) ) {
 PHPWS_Core::initModClass('hms', 'HMS_Student.php');
 PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
 $_SESSION['login_as_student'] = true;

 //Don't try to set the asu_username if it's already set
 if(!isset($_SESSION['asu_username'])){
 $_SESSION['asu_username']     = $_REQUEST['login_as_student'];
 }

 HMS_Login::student_login($_SESSION['asu_username']);
 HMS_Activity_Log::log_activity($_SESSION['asu_username'], ACTIVITY_LOGIN_AS_STUDENT, Current_User::getUsername(), '');
 } else if( isset($_REQUEST['end_student_session']) ) {
 unset($_SESSION['login_as_student']);
 unset($_SESSION['asu_username']);
 unset($_SESSION['application_term']);
 header('Location: index.php?module=hms&type=maintenance&op=show_maintenance_options');
 exit;
 }
 Layout::add('<div style="background: #eceff5; border-style: solid; border-width: thin; font-size: large; font-weight: bold; text-align: center; padding: 15px; width: 900px; margin-left: 5px;"><a href=index.php?module=hms&end_student_session=true><img height=24px src="images/mod/hms/icons/log-out.png" /> Logout of student Session </a></div>');
 } else {
 # Someone is being naughty...
 //exit();
 unset($_SESSION);
 header('Location: index.php');
 exit;
 }
 }
 if($type == NULL) {
 if( $username == 'hms_student' || (Current_User::allow('hms', 'login_as_student') && isset($_SESSION['login_as_student'])) ) $type = STUDENT;
 else $type = ADMIN;
 }

 switch($type)
 {
 case STUDENT:
 PHPWS_Core::initModClass('hms', 'HMS_Student.php');
 $content = HMS_Student::main();
 break;
 case ADMIN:
 PHPWS_Core::initModClass('hms', 'HMS_Admin.php');
 $content = HMS_Admin::main();
 break;
 default:
 $content = "wtf?";
 break;
 }
 Layout::add($content);
 }
 }
 }
 */

?>
