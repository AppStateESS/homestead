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
		$rows = $this->report->getRows();
		$this->tpl['rows'] = $rows;

	    $this->tpl['TERM'] = Term::toString($this->report->getTerm());

		$this->tpl['TOTAL'] = count($rows);

	    return PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/RaReport.tpl');
	}
}
