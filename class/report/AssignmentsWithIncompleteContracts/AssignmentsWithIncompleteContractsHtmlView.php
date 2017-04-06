<?php

class AssignmentsWithIncompleteContractsHtmlView extends ReportHtmlView
{

    protected function render()
    {
        parent::render();

        $this->tpl['TERM'] = Term::toString($this->report->getTerm());

        $this->tpl['rows'] = $this->report->getData();

        return PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/AssignmentsWithIncompleteContracts.tpl');
    }

}
