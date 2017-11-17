<?php

namespace Homestead;

use \Homestead\Exception\PermissionException;
use \Homestead\Command\CommandContext;

/**
 * Primary HMS class
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

abstract class HMS {

    protected $context;

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
        if(\Current_User::isLogged() && !isset($_SESSION['HMS_LOGGED_THE_LOGIN'])) {
            $username = strtolower(\Current_User::getUsername());
            HMS_Activity_Log::log_activity($username,ACTIVITY_LOGIN, $username, NULL);
            $_SESSION['HMS_LOGGED_THE_LOGIN'] = $username;
        }

        if(!\Current_User::isLogged() && $this->context->get('action') != 'ShowFrontPage'){
            \NQ::simple('hms', NotificationView::ERROR, 'You must be logged in to do that.');
            $action = 'ShowFrontPage';
        }else{
            $action = $this->context->get('action');
        }

        $cmd = CommandFactory::getCommand($action);

        // Tell NewRelic about the controller we're going to run, so we get
        // better transaction names than just all 'index.php'
        if (extension_loaded('newrelic')) { // Ensure PHP agent is available
            newrelic_name_transaction($action);
        }

        if(HMS_DEBUG){
            $cmd->execute($this->context);
        }else{
            try {
                $cmd->execute($this->context);
            } catch(PermissionException $p) {
                \NQ::Simple('hms', NotificationView::ERROR, 'You do not have permission to perform that action. If you believe this is an error, please contact University Housing.');
                $nv = new NotificationView();
                $nv->popNotifications();
                \Layout::add($nv->show());
            } catch(\Exception $e) {

                $user = \Current_User::getUserObj();
                $e->username = $user->getUsername();

                if(isset($_SERVER['HTTP_REFERER'])){
                    $e->referrer = $_SERVER['HTTP_REFERER'];
                }else{
                    $e->referrer = 'None';
                }

                $e->remoteAddr = $_SERVER['REMOTE_ADDR'];

                if (extension_loaded('newrelic')) { // Ensure PHP agent is available
                    newrelic_notice_error($e->getMessage(), $e);
                }

                try {
                    $message = $this->formatException($e);
                    \NQ::Simple('hms', NotificationView::ERROR, 'An internal error has occurred, and the authorities have been notified.  We apologize for the inconvenience.');
                    $this->emailError($message);
                    $nv = new NotificationView();
                    $nv->popNotifications();
                    \Layout::add($nv->show());
                } catch(\Exception $e) {
                    $message2 = $this->formatException($e);
                    echo "HMS has experienced a major internal error.  Attempting to email an admin and then exit.";
                    $message = "Something terrible has happened, and the exception catch-all threw an exception.\n\nThe first exception was:\n\n$message\n\nThe second exception was:\n\n$message2";
                    mail(FROM_ADDRESS, 'A Major HMS Error Has Occurred', $message);
                    exit();
                }
            }
        }
    }

    protected function formatException(\Exception $e)
    {
        ob_start();
        echo "Ohes Noes!  HMS threw an exception that was not caught!\n\n";
        echo "Host: {$_SERVER['SERVER_NAME']}({$_SERVER['SERVER_ADDR']})\n";
        echo 'Request time: ' . date("D M j G:i:s T Y", $_SERVER['REQUEST_TIME']) . "\n";
        if(isset($_SERVER['HTTP_REFERER'])){
            echo "Referrer: {$_SERVER['HTTP_REFERER']}\n";
        }else{
            echo "Referrer: (none)\n";
        }
        echo "Remote addr: {$_SERVER['REMOTE_ADDR']}\n\n";

        $user = \Current_User::getUserObj();
        if(isset($user) && !is_null($user)){
            echo "User name: {$user->getUsername()}\n\n";
        }else{
            echo "User name: (none)\n\n";
        }

        echo "Here is the exception:\n\n";
        echo print_r($e, true);

        echo "\n\nHere is the CommandContext:\n\n";
        echo print_r($this->context, true);

        echo "\n\nHere is REQUEST:\n\n";
        echo print_r($_REQUEST, true);

        echo "\n\nHere is CurrentUser:\n\n";
        echo print_r(\Current_User::getUserObj(), true);

        $message = ob_get_contents();
        ob_end_clean();

        return $message;
    }

    protected function emailError($message)
    {
        //$to = HMSSettings::getUberAdminEmail();
        $to = HMS_Email::get_tech_contacts();

        $tags = array('MESSAGE' => $message);
        HMS_Email::send_template_message($to, 'Uncaught Exception', 'email/UncaughtException.tpl', $tags);
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
        \NQ::close();
        exit();
    }
}
