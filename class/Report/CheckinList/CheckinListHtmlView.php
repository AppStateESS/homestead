<?php

namespace Homestead\Report\CheckinList;

/**
 * HTML View for CheckinList report
 *
 * @author Jeremy Booker
 * @package HMS
 */

class CheckinListHtmlView extends ReportHtmlView {

    protected function render()
    {
        parent::render();

        $this->tpl['TERM'] = Term::toString($this->report->getTerm());
        $this->tpl['TOTAL'] = $this->report->getTotal();

        // Copy results into the template
        foreach($this->report->getData() as $row){
            $this->tpl['rows'][] = $row;
        }

        return \PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/CheckinList.tpl');
    }
}
