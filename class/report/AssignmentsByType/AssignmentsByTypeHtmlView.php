<?php

namespace Homestead\report\AssignmentsByType;

class AssignmentsByTypeHtmlView extends ReportHtmlView {

    protected function render(){
        parent::render();

        $this->tpl['TERM'] = Term::toString($this->report->getTerm());

        $rows = array();

        $totalAssignments = 0;

        foreach($this->report->getTypeCounts() as $result){
            $row = array();

            // Translate the reason string into a human readable label, if it exists
            // Otherwsie, use the raw reason code
            $name = constant($result['reason']);
            if(isset($name)){
                $row['REASON'] = $name;
            }else{
                $row['REASON'] = $result['reason'];
            }

            $row['COUNT'] = $result['count'];

            $rows[] = $row;

            // Add the count for this reason to the running total
            $totalAssignments += $result['count'];
        }

        $this->tpl['TABLE_ROWS'] = $rows;

        $this->tpl['TOTAL_ASSIGNMENTS'] = $totalAssignments;

        return \PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/AssignmentsByType.tpl');
    }
}
