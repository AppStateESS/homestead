<?php

namespace Homestead\report\ReappAvailableBeds;

class ReappAvailableBedsHtmlView extends ReportHtmlView
{

    protected function render()
    {
        parent::render();

        $this->tps['TERM'] = Term::toString($this->report->getTerm());

        // Copy results into the template
        foreach($this->report->getData() as $row){
            $this->tpl['halls'][] = $row;
        }

        return \PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/ReappAvailableBeds.tpl');
    }

}
