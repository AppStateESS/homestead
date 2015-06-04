<?php

class CheckinsByHallHtmlView extends ReportHtmlView {

    protected function render(){
        parent::render();

        $this->tpl['TERM'] = Term::toString($this->report->getTerm());

        $rows = array();

        $totalAssignments = 0;

        foreach($this->report->getCheckinCounts() as $hall=>$count){
            $row = array();

            $row['HALL_NAME'] = $hall;

            $row['COUNT'] = $count;

            $rows[] = $row;

            $totalAssignments += $count;
        }

        $this->tpl['TABLE_ROWS'] = $rows;

        $this->tpl['TOTAL_CHECKINS'] = $totalAssignments;

        return PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/CheckinsByHall.tpl');
    }
}

