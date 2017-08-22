<?php

namespace Homestead\report\ApplicantDemographics;

class ApplicantDemographicsHtmlView extends ReportHtmlView {

    protected function render()
    {
        parent::render();
        $this->tpl['TERM'] = Term::toString($this->report->getTerm());

        // Males
        foreach($this->report->getMaleTotals() as $total){
            $this->tpl['male_totals'][] = array('COUNT'=>$total);
        }

        $this->tpl['MALE_SUB'] = $this->report->getMaleSubTotal();

        // Females
        foreach($this->report->getFemaleTotals() as $total){
            $this->tpl['female_totals'][] = array('COUNT'=>$total);
        }

        $this->tpl['FEMALE_SUB'] = $this->report->getFemaleSubTotal();

        // Type totals
        foreach($this->report->getTypeTotals() as $total){
            $this->tpl['type_totals'][] = array('COUNT'=>$total);
        }

        $this->tpl['SUB_TOTAL'] = $this->report->getSubTotal();

        // Cancelled totals
        $cancelledTotals = $this->report->getCancelledTotals();
        $this->tpl['FEMALE_CANCELLED'] = $cancelledTotals[FEMALE];
        $this->tpl['MALE_CANCELLED']   = $cancelledTotals[MALE];

        $this->tpl['CANCELLED_SUB'] = $this->report->getCancelledSubTotal();

        $this->tpl['FEMALE_TOTAL'] = $this->report->getFemaleGrandTotal();
        $this->tpl['MALE_TOTAL']   = $this->report->getMaleGrandTotal();

        $this->tpl['ALL_TOTAL'] = $this->report->getTotal();

        return \PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/ApplicantDemographics.tpl');
    }
}
