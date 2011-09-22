<?php

/**
 * HTML View for Reapplication Overview report
 *
 * @author Jeremy Booker
 * @package HMS
 */

class ReapplicationOverviewHtmlView extends ReportHtmlView {

    protected function render()
    {
        parent::render();

        $this->tpl['TERM'] = Term::toString($this->report->getTerm());
        
        // Copy keys and values from the report. Kinda a hack
        foreach($this->report->getData() as $key=>$value){
            $this->tpl[$key] = $value;
        }
        
        return PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/ReapplicationOverview.tpl');
    }
}

?>