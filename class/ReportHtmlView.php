<?php

PHPWS_Core::initModClass('hms', 'ReportView.php');

abstract class ReportHtmlView extends ReportView {
    
    protected $tpl;
    protected $output;
    
    public function __construct(Report $report)
    {
        parent::__construct($report);
        
        $this->tpl = array();
        $this->output = null;
    }
    
    protected function render()
    {
        $this->tpl['NAME'] = $this->report->getFriendlyName();
        $this->tpl['EXEC_DATE'] = HMS_Util::get_long_date_time($this->report->getCompletedTimestamp());
        $this->tpl['EXEC_USER'] = $this->report->getCreatedBy();
    }
    
    public function show()
    {
        if(is_null($this->output)){
            $this->output = $this->render();
        }
    
        return $this->output;
    }
    
    public function getWrappedHtml()
    {
        return Layout::wrap($this->show());
    }
}

?>