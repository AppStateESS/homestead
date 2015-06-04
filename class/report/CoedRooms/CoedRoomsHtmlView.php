<?php

/**
 * HTML View for TwentyOne report
 *
 * @author John Felipe
 * @package HMS
 */

class CoedRoomsHtmlView extends ReportHtmlView 
{
	protected function render()
    {
		parent::render();
		$this->tpl['rows'] = $this->report->getRows();

	    $this->tpl['TERM'] = Term::toString($this->report->getTerm());

	    $this->tpl['totalCoed'] = $this->report->getTotalCoed();

	    return PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/CoedRooms.tpl');
	}
}

