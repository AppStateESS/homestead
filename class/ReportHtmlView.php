<?php

PHPWS_Core::initModClass('hms', 'ReportView.php');

/**
 * ReportHtmlView
 * 
 * Provides a parent class and shared functionality
 * for rendering a report in HTML. Each report can
 * extend this class to provide its own HTML view.
 * 
 * @author jbooker
 * @package HMS
 */
abstract class ReportHtmlView extends ReportView {
    
    protected $tpl; // Array of template variables
    protected $output; // Finished output, used to give WrappHtml without re-processing the template

    /**
     * Constructor
     * 
     * Saves the report and initializes local storage.
     * 
     * @param Report $report
     */
    public function __construct(Report $report)
    {
        parent::__construct($report);
        
        $this->tpl = array();
        $this->output = null;
    }

    /**
     * Uses the report stored in this view to provide template variables.
     * This method provides some common template variables, but should be
     * extended by subclasses.
     */
    protected function render()
    {
        $this->tpl['NAME'] = $this->report->getFriendlyName();
        $this->tpl['EXEC_DATE'] = HMS_Util::get_long_date_time($this->report->getCompletedTimestamp());
        $this->tpl['EXEC_USER'] = $this->report->getCreatedBy();
    }
    
    /**
     * Renders the template tags, processes the template, and stores the
     * output locally (if the output hasn't been generated already). Returns
     * the finished output (a snippit of HTML markup) for this report.
     * 
     * @return String finished HTML output
     */
    public function show()
    {
        if(is_null($this->output)){
            $this->output = $this->render();
        }
    
        return $this->output;
    }
    
    /**
     * Uses show() to get the HTML snippit for this report, then uses PHPWS_Layout to
     * wrap that snippit in a fully-formed HTML document, suitable for independent viewing.
     * 
     * @see show()
     * @return String fully-formed HTML document
     */
    public function getWrappedHtml()
    {
        return Layout::wrap($this->show());
    }
}
