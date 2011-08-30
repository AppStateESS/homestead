<?php

/**
 * ScheduleReportCommand
 * 
 * Command/controller class responsible for scheduling a
 * report to be executed in the background either now, or
 * at some point in the future.
 * 
 * @author jbooker
 * @package HMS
 */

class ScheduleReportCommand extends Command {
    
    public function getRequestVars(){
        return array('action'=>'ScheduleReport');
    }
    
    public function execute(CommandContext $context)
    {
        // TODO Check permissions

        PHPWS_Core::initModClass('hms', 'ReportFactory.php');
        
        $reportClass = $context->get('reportClass');
        
        if(!isset($reportClass) || is_null($reportClass)){
            throw new InvalidArgumentException('Missing report class name.');
        }
        
        $reportCtrl = ReportFactory::getcontrollerInstance($reportClass);
        
        $runNow = $context->get('runNow');
        if(isset($runNow) && $runNow == "true"){
            $time = time();
        }else{
            $timePicker = $context->get('timePicker');
            $timeParts = explode(" ", $timePicker);
            $meridian = $timeParts[1];
            $timeParts = explode(":", $timeParts[0]);
            
            $hour = $timeParts[0];
            
            if($meridian == "PM"){
                $hour += 12;
            }
            
            $min  = $timeParts[1];
            
            $datePicker = $context->get('datePicker');
            $dateParts = explode("/", $datePicker);
            $month = $dateParts[0];
            $day   = $dateParts[1];
            $year  = $dateParts[2];
            
            $time = mktime($hour, $min, 0, $month, $day, $year);
        }
        
        // Set the exec time
        $reportCtrl->newReport($time);

        // Save the report
        $reportCtrl->saveReport();
        
        // Grab the report's settings from the context
        $reportCtrl->setParams($context->getParams());
        
        // Save those params
        $reportCtrl->saveParams();
        
        HMS::quit();
    }
}

?>