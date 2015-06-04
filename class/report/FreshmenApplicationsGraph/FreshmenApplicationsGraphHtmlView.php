<?php

class FreshmenApplicationsGraphHtmlView extends ReportHtmlView {
    
    protected function render()
    {
        parent::render();
        $this->tpl['TERM'] = Term::toString($this->report->getTerm());
        
        $this->tpl['lastTerm'] = Term::toString($this->report->getLastTerm());
        $this->tpl['thisTerm'] = Term::toSTring($this->report->getTerm());
        
        $this->tpl['lastYearSeries'] = $this->report->getLastYearJson();
        $this->tpl['thisYearSeries'] = $this->report->getThisYearJson();
        
        return PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/FreshmenApplicationsGraph.tpl');
    }
}

