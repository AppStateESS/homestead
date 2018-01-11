<?php

namespace Homestead\Report\CancelledAppsByType;

use \Homestead\ReportHtmlView;
use \Homestead\Term;
use \Homestead\HMS_Util;

/**
 * HTML view for the Cancelled Housing Applications by Student Type report.
 *
 * @author jbooker
 * @package HMS
 */
class CancelledAppsByTypeHtmlView extends ReportHtmlView {

    protected function render(){
        parent::render();

        $this->tpl['TERM'] = Term::toString($this->report->getTerm());

        $rows = array();

        $totalCancellations = 0;

        foreach($this->report->getTypeCounts() as $type=>$count){
            $row = array();

            $row['TYPE'] = HMS_Util::formatType($type);

            $row['COUNT'] = $count;

            $rows[] = $row;

            $totalCancellations += $count;
        }

        $this->tpl['TABLE_ROWS'] = $rows;

        $this->tpl['TOTAL_CANCELLATIONS'] = $totalCancellations;

        return \PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/CancelledAppsByType.tpl');
    }
}
