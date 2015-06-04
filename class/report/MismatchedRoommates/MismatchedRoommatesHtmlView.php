<?php

/**
 * HTML View for MismatchedRoommates report
 *
 * @author Chris Detsch
 * @package HMS
 */

class MismatchedRoommatesHtmlView extends ReportHtmlView
{
    protected function render()
    {
        parent::render();

        $this->tpl['TERM'] = Term::toString($this->report->getTerm());

        $this->tpl['MISMATCH_COUNT'] = $this->report->getMismatchCount();


        foreach($this->report->getData() as $row)
        {
            $this->tpl['rows'][] = $row;
        }

        return PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/MismatchedRoommates.tpl');
    }

}


