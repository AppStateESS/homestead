<?php

/**
 * HTML View for NoShowList report
 *
 * @author Jeremy Booker
 * @package HMS
 */

class NoShowListHtmlView extends ReportHtmlView {

    protected function render()
    {
        parent::render();

        $this->tpl['TERM'] = Term::toString($this->report->getTerm());
        $this->tpl['TOTAL'] = $this->report->getTotal();

        // Copy results into the template
        foreach($this->report->getData() as $row){
            $this->tpl['rows'][] = $row;
        }

        return PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/NoShowList.tpl');
    }
}

