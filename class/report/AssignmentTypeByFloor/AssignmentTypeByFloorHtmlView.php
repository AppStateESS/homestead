<?php

class AssignmentTypeByFloorHtmlView extends ReportHtmlView {

    protected function render(){
        parent::render();

        $this->tpl['TERM'] = Term::toString($this->report->getTerm());

        $halls = $this->report->getHalls();

        // Need to create our own Template object
        $myTpl = new PHPWS_Template('hms');
        $myTpl->setFile('admin/reports/AssignmentTypeByFloor.tpl');

        // Set the existing tags
        $myTpl->setData($this->tpl);

        $hallTags = array();

        foreach($halls as $hall){
            $hallCounts = $this->report->getCountsForHall($hall);

            // Hall totals summary
            foreach($hallCounts as $hallCount){
                $myTpl->setCurrentBlock('hall_totals');
                $myTpl->setData(array('HALL_TOTAL_TYPE'=>constant($hallCount['reason']), 'HALL_TOTAL_COUNT'=>$hallCount['count']));
                $myTpl->parseCurrentBlock();
            }

            $floors = $hall->get_floors();

            foreach($floors as $floor){
                $counts = $this->report->getCountsForFloor($floor);

                foreach($counts as $countRow){
                    $myTpl->setCurrentBlock('floor_counts');
                    $myTpl->setData(array('TYPE'=>constant($countRow['reason']), 'COUNT'=>$countRow['count']));
                    $myTpl->parseCurrentBlock();
                }

                // Floor tags
                $myTpl->setCurrentBlock('floors');
                $myTpl->setData(array('FLOOR_NUMBER'=>HMS_Util::ordinal($floor->getFloorNumber())));
                $myTpl->parseCurrentBlock();
            }

            // Set hall tags
            $hallTags['HALL_NAME']      = $hall->getHallName();
            $hallTags['HALL_OCCUPANCY'] = $hall->get_number_of_assignees();
            $hallTags['HALL_CAPACITY']  = $hall->countNominalBeds();
            $myTpl->setCurrentBlock('hall_repeat');
            $myTpl->setData($hallTags);
            $myTpl->parseCurrentBlock();
        }

        //return PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/AssignmentTypeByFloor.tpl');
        return $myTpl->get();
    }
}
