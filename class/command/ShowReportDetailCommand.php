<?php

class ShowReportDetailCommand extends Command {
    
    private $reportClass;
    
    public function setReportClass($class)
    {
        $this->reportClass = $class;
    }
    
    public function getRequestVars()
    {
        if(!isset($this->reportClass) || is_null($this->reportClass)){
            throw new InvalidArgumentException('Missing report class.');
        }
        
        return array('action'=>'ShowReportDetail', 'reportClass'=>$this->reportClass);
    }
    
    public function execute(CommandContext $context)
    {
        $class = $context->get('reportClass');
        
        if(!isset($class) || is_null($class)){
            throw new InvalidArgumentException('Missing report class.');
        }
        
        PHPWS_Core::initModClass('hms', 'ReportFactory.php');
        PHPWS_Core::initModClass('hms', 'ReportDetailView.php');
        
        $reportCtl = ReportFactory::getControllerInstance($class);
        $view = new ReportDetailView($reportCtl);
        
        $context->setContent($view->show());
    }
}

?>