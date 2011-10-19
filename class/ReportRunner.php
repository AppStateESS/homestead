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

class ReportRunner extends ScheduledPulse
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
    public function execute()
    {
        // Reschedule the next run of this process
        $sp = $this->makeClone();
        $sp->execute_at = strtotime("+1 minutes");
        $sp->save();
        
        // Load necessary classes
        PHPWS_Core::initModClass('hms', 'HMS.php');
        PHPWS_Core::initModClass('hms', 'UserStatus.php');
        PHPWS_Core::initModClass('hms', 'ReportFactory.php');
        
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
        if(!isset($results) || is_null($results) || empty($results)){
            UserStatus::removeMask();
            return;
        }
        
        // Run each report
        foreach($results as $report){
            try{
                // Load the proper controller for this report
                $reportCtrl = ReportFactory::getControllerById($report['id']);
                
                // Load this report's params
                $reportCtrl->loadParams();
                
                // Generate the report
                $reportCtrl->generateReport();
                
            }catch(Exception $e){
                // TODO handle the exception nicely
                echo 'Exception!';
                print_r($e);
                exit;
            }
        }
        
        
        // Remove the mask
        UserStatus::removeMask();
        
        // Exit cleanly
        return;
    }
}
?>