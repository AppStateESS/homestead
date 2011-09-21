<?php

/**
 * HTML View for TwentyOne report
 *
 * @author Jeremy Booker
 * @package HMS
 */

class GenderDistributionByHallHtmlView extends ReportHtmlView {

    protected function render()
    {
        parent::render();

        $this->tpl['rows'] = $this->report->getRows();

        $this->tpl['TERM'] = Term::toString($this->report->getTerm());

        return PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/GenderDistributionByHall.tpl');
    }
}

?>