<?php

/**
 * HTML View for TwentyOne report
 *
 * @author Jeremy Booker
 * @package HMS
 */

class TwentyOneHtmlView extends ReportHtmlView {

    protected function render()
    {
        parent::render();

        $this->tpl['rows'] = $this->report->getRows();

        $this->tpl['TERM'] = Term::toString($this->report->getTerm());
        
        $this->tpl['totalCurrOccupancy'] = $this->report->getTotalCurrOccupancy();
        
        $this->tpl['totalMales'] = $this->report->getTotalMales();
        $this->tpl['totalFemales'] = $this->report->getTotalFemales();
        
        $this->tpl['totalMalePercent'] = $this->report->getTotalMalePercent();
        $this->tpl['totalFemalePercent'] = $this->report->getTotalFemalePercent();

        return PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/TwentyOne.tpl');
    }
}

?>