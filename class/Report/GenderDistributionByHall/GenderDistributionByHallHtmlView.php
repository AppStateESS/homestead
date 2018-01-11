<?php

namespace Homestead\Report\GenderDistributionByHall;

use \Homestead\ReportHtmlView;
use \Homestead\Term;

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

        $this->tpl['totalCurrOccupancy'] = $this->report->getTotalCurrOccupancy();

        $this->tpl['totalMales'] = $this->report->getTotalMales();
        $this->tpl['totalFemales'] = $this->report->getTotalFemales();
        $this->tpl['totalCoed'] = $this->report->getTotalCoed();


        $this->tpl['totalMalePercent'] = $this->report->getTotalMalePercent();
        $this->tpl['totalFemalePercent'] = $this->report->getTotalFemalePercent();
        $this->tpl['totalCoedPercent'] = $this->report->getTotalCoedPercent();

        return \PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/GenderDistributionByHall.tpl');
    }
}
