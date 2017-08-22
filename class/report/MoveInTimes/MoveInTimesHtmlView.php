<?php

namespace Homestead\report\MoveInTimes;

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

class MoveInTimesHtmlView extends ReportHtmlView {

    protected function render()
    {
        parent::render();
        $rows = $this->report->getRows();

        $tpl = new \PHPWS_Template('hms');
        $tpl->setFile('admin/reports/MoveInTimes.tpl');
        foreach ($rows as $hall) {
            $tpl->setCurrentBlock('floor-rows');
            foreach ($hall['floor_rows'] as $floor) {
                $tpl->setData($floor);
                $tpl->parseCurrentBlock();
            }
            unset($hall['floor-rows']);
            $tpl->setCurrentBlock('hall-rows');
            $tpl->setData($hall);
            $tpl->parseCurrentBlock();
        }

        $this->tpl['TERM'] = Term::toString($this->report->getTerm());
        $tpl->setCurrentBlock();
        $tpl->setData($this->tpl);
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

}
