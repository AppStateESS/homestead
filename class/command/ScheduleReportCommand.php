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
            $time = 0; //TODO mktime
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