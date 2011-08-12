<?php

class ApplicantDemographicsHTMLView extends ReportHtmlView {

    protected function render()
    {
        parent::render();
        $this->tpl['TERM'] = Term::toString($this->report->getTerm());
        
        // Males
        foreach($this->report->getMaleTotals() as $total){
            $this->tpl['male_totals'][] = array('COUNT'=>$total);
        }
        
        $this->tpl['MALE_SUM'] = $this->report->getMaleSum();
        
        // Females
        foreach($this->report->getFemaleTotals() as $total){
            $this->tpl['female_totals'][] = array('COUNT'=>$total);
        }
        
        $this->tpl['FEMALE_SUM'] = $this->report->getFemaleSum();
        
        // Type totals
        foreach($this->report->getTypeTotals() as $total){
            $this->tpl['type_totals'][] = array('COUNT'=>$total);
        }
        
        $this->tpl['ALL_TOTAL'] = $this->report->getTotal();
        
        return PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/application_demographics.tpl');
    }
}

?>