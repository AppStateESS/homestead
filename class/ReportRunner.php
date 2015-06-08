<?php

/**
 * Handles running scheduled reports in the background.
 * Invoked by Pulse once a minute. Upon being invoked,
 * it checks for pending reports, runs them, and reschedules
 * itself for the next minute.
 *
 * @author Jeremy Booker
 * @package HMS
 */
require_once PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php';

class ReportRunner
{
    /**
     * Constructor - Sets up some variables needed by
     * the parent class' constructor.
     *
     * @param integer $id Id of an existing pulse to load
     */
    public function __construct($id = null)
    {
        $this->module = 'hms';
        $this->class_file = 'ReportRunner.php';
        $this->class = 'ReportRunner';

        parent::__construct($id);
    }

    /**
     * Executes this pulse. Checks for any pending reports and runs them.
     */
    public static function execute()
    {
        // Reschedule the next run of this process
        /*
          $sp = $this->makeClone();
          $sp->execute_at = strtotime("+1 minutes");
          $sp->save();
         * 
         */

        // Load necessary classes
        PHPWS_Core::initModClass('hms', 'UserStatus.php');
        PHPWS_Core::initModClass('hms', 'ReportFactory.php');
        PHPWS_Core::initModCLass('hms', 'HMS_Email.php');

        // Fake a user, in case we need that
        UserStatus::wearMask('HMS System');


        // Check for any pending reports (scheduled for any time up until now)
        $db = new PHPWS_DB('hms_report');
        $db->addWhere('completed_timestamp', null, 'IS'); // not completed
        $db->addWhere('began_timestamp', null, 'IS'); // not already running somewhere
        $db->addWhere('scheduled_exec_time', time(), '<='); // scheduled exec time is now or before
        $db->addOrder('scheduled_exec_time ASC'); // Run in order scheduled

        $results = $db->select();

        // If there's nothing to do, quite nicely
        if (!isset($results) || is_null($results) || empty($results)) {
            UserStatus::removeMask();
            return;
        }

        // Run each report
        foreach ($results as $row) {
            $report = null;

            try {
                // Load the proper controller for this report
                $reportCtrl = ReportFactory::getControllerById($row['id']);

                // Load this report's params
                $reportCtrl->loadParams();

                // Generate the report
                $reportCtrl->generateReport();

                $report = $reportCtrl->getReport();
            } catch (Exception $e) {
                // handle the exception nicely
                self::emailError(self::formatException($e));
                exit;
            }

            // Send success notification
            $username = $report->getCreatedBy();
            if ($username == 'jbooker') {
                $username = 'jb67803';
            }

            HMS_Email::sendReportCompleteNotification($username, $report->getFriendlyName());
        }

        // Remove the mask
        UserStatus::removeMask();

        // Exit cleanly
        return;
    }

    private static function formatException(Exception $e)
    {
        ob_start();
        echo "Ohes Noes!  An HMS report threw an exception that was not caught!\n\n";
        echo "Host: {$_SERVER['SERVER_NAME']}({$_SERVER['SERVER_ADDR']})\n";
        echo 'Request time: ' . date("D M j G:i:s T Y", $_SERVER['REQUEST_TIME']) . "\n";
        if (isset($_SERVER['HTTP_REFERER'])) {
            echo "Referrer: {$_SERVER['HTTP_REFERER']}\n";
        } else {
            echo "Referrer: (none)\n";
        }
        echo "Remote addr: {$_SERVER['REMOTE_ADDR']}\n\n";

        $user = Current_User::getUserObj();
        if (isset($user) && !is_null($user)) {
            echo "User name: {$user->getUsername()}\n\n";
        } else {
            echo "User name: (none)\n\n";
        }

        echo "Here is the exception:\n\n";
        print_r($e);

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
        HMS_Email::send_template_message($to, '[hms] Uncaught Report Exception', 'email/UncaughtException.tpl', $tags);
    }

}
