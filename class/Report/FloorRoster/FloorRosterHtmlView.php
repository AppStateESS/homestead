<?php

namespace Homestead\Report\FloorRoster;

use \Homestead\ReportHtmlView;

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

class FloorRosterHtmlView extends ReportHtmlView {

    public function render()
    {
        $hall = null;
        $tpl = new \PHPWS_Template('hms');
        $tpl->setFile('admin/reports/FloorRoster.tpl');

        $rows = & $this->report->rows;

        foreach ($rows as $hall_name => $hall) {

            foreach ($hall as $row) {
                $row['bedroom_label'] = strtoupper($row['bedroom_label']);

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
