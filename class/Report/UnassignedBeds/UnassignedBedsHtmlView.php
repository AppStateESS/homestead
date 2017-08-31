<?php

namespace Homestead\Report\UnassignedBeds;

use \Homestead\ReportHtmlView;
use \Homestead\Term;

/**
 * HTML View for UnassignedBeds report
 *
 * @author Jeremy Booker
 * @package HMS
 */

class UnassignedBedsHtmlView extends ReportHtmlView {

    protected function render()
    {
        parent::render();

        $this->tpl['TERM'] = Term::toString($this->report->getTerm());
        $this->tpl['TOTAL_BEDS'] = $this->report->getTotalBeds();
        $this->tpl['TOTAL_ROOMS'] = $this->report->getTotalRooms();
        $this->tpl['MALE'] = $this->report->getMale();
        $this->tpl['FEMALE'] = $this->report->getFemale();
        $this->tpl['COED'] = $this->report->getCoed();

        // Copy results into the template
        foreach($this->report->getData() as $row){
            if(empty($row['maleRooms']))
            {
              $row['maleRooms'] = "None";
            }
            if(empty($row['femaleRooms']))
            {
              $row['femaleRooms'] = "None";
            }
            if(empty($row['coedRooms']))
            {
              $row['coedRooms'] = "None";
            }
            $this->tpl['rows'][] = $row;
        }

        return \PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/UnassignedBeds.tpl');
    }
}
