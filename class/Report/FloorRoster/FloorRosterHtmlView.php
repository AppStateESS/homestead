<?php

namespace Homestead\Report\FloorRoster;

use \Homestead\ReportHtmlView;

/*
 *
 * @author Ted Eberhard <eberhardtm at appstate dot edu>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

class FloorRosterHtmlView extends ReportHtmlView {

    public function render() {
        $hall = null;
        $tpl = new \PHPWS_Template('hms');
        $tpl->setFile('admin/reports/FloorRoster.tpl');

        $rows = & $this->report->rows;

        foreach ($rows as $hall_name => $hall) {

            foreach ($hall as $row) {
                $row['bedroom_label'] = strtoupper($row['bedroom_label']);
                $row['over_21'] = "No";
                // Calculate the timestamp from 21 years ago
                $twentyOneYearsAgo = strtotime("-21 years");
                $DOB = strtotime($row['dob']);
                if (strtotime($row['dob']) < $twentyOneYearsAgo) {
                    $row['over_21'] = "Yes";
                }

                $tpl->setCurrentBlock('room-rows');
                $tpl->setData($row);
                $tpl->parseCurrentBlock();
            }

            $tpl->setCurrentBlock('hall-rows');
            $tpl->setData(array('HALL' => $hall_name));
            $tpl->parseCurrentBlock();
        }

        $content = $tpl->get();
        return $content;
    }

}
