<?php

namespace Homestead\report\CancelledAppsByReason;

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
        $reasons = $this->report->getReasons();

        // All Students
        $rows = array();

        $totalCancellations = 0;

        foreach($this->report->getReasonCounts() as $reason=>$count){
            $row = array();

            $row['ALL_REASON'] = $reasons[$reason];
            $row['ALL_COUNT']  = $count;
            $rows[] = $row;

            $totalCancellations += $count;
        }

        $this->tpl['TABLE_ROWS'] = $rows;
        $this->tpl['TOTAL_CANCELLATIONS'] = $totalCancellations;


        // Freshmen
        $freshmenRows = array();
        $freshmenCancellations = 0;

        foreach($this->report->getFreshmenReasonCounts() as $reason=>$count){
            $row = array();
            $row['FR_REASON'] = $reasons[$reason];
            $row['FR_COUNT'] = $count;

            $freshmenRows[] = $row;
            $freshmenCancellations += $count;
        }

        $this->tpl['FRESHMEN_ROWS'] = $freshmenRows;
        $this->tpl['FRESHMEN_TOTAL'] = $freshmenCancellations;


        // Countinuing
        $continuingRows = array();
        $continuingCancellations = 0;

        foreach($this->report->getContinuingReasonCounts() as $reason=>$count){
            $row = array();
            $row['C_REASON'] = $reasons[$reason];
            $row['C_COUNT'] = $count;

            $continuingRows[] = $row;
            $continuingCancellations += $count;
        }

        $this->tpl['CONTINUING_ROWS'] = $continuingRows;
        $this->tpl['CONTINUING_TOTAL'] = $continuingCancellations;


        return \PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/CancelledAppsByReason.tpl');
    }
}
