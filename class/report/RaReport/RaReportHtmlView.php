<?php

/**
 * HTML View for Ra Report.
 *
 * @author John Felipe
 * @package HMS
 */

class RaReportHtmlView extends ReportHtmlView 
{
	protected function render()
    {
		parent::render();
		$this->tpl['rows'] = $this->report->getRows();

	    $this->tpl['TERM'] = Term::toString($this->report->getTerm());

	    

	    return PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/RaReport.tpl');
	}
}


