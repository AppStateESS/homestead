<?php

class AssignmentsByTypeHtmlView extends ReportHtmlView {
    
    protected function render(){
        parent::render();
        
        $this->tpl['TERM'] = Term::toString($this->report->getTerm());
        
        $rows = array();
        
        $totalAssignments = 0;
        
        foreach($this->report->getTypeCounts() as $reason=>$count){
            $row = array();
            
            $name = constant($reason);
            if(isset($name)){
                $row['REASON'] = $name;
            }else{
                $row['REASON'] = $reason;
            } 
            
            $row['COUNT'] = $count;
            
            $rows[] = $row;
            
            $totalAssignments += $count;
        }

        $this->tpl['TABLE_ROWS'] = $rows;
        
        $this->tpl['TOTAL_ASSIGNMENTS'] = $totalAssignments;
        
        return PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/AssignmentsByType.tpl');
    }
}

