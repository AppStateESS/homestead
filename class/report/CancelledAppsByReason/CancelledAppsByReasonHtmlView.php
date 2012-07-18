<?php

/**
 * HTLM view for Cancelled Housing Applications by Reason report.
 * 
 * @author Jeremy Booker
 * @package HMS
 */

class CancelledAppsByReasonHtmlView extends ReportHtmlView {
    
    protected function render()
    {
        parent::render();
        
        $this->tpl['TERM'] = Term::toString($this->report->getTerm());

        $rows = array();
        
        $totalCancellations = 0;
        
        $reasons = $this->report->getReasons();
        
        foreach($this->report->getReasonCounts() as $reason=>$count){
            $row = array();
            
            $row['REASON'] = $reasons[$reason];
            $row['COUNT']  = $count;
            
            $rows[] = $row;
            
            $totalCancellations += $count;
        }
        
        $this->tpl['TABLE_ROWS'] = $rows;
        
        $this->tpl['TOTAL_CANCELLATIONS'] = $totalCancellations;
        
        return PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/CancelledAppsByReason.tpl');
    }
}

?>
