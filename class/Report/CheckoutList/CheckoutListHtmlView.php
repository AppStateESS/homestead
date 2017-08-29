<?php

namespace Homestead\Report\CheckoutList;

/**
 * HTML View for CheckoutList report
 *
 * @author Chris Detsch
 * @package HMS
 */

class CheckoutListHtmlView extends ReportHtmlView
{

    protected function render()
    {
        parent::render();

        $this->tpl['TERM'] = Term::toString($this->report->getTerm());
        $this->tpl['TOTAL'] = $this->report->getTotal();

        // Copy results into the template
        foreach($this->report->getData() as $row){
            $this->tpl['rows'][] = $row;
        }

        return \PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/CheckoutList.tpl');
    }
}
