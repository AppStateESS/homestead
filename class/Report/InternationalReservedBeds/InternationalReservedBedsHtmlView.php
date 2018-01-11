<?php

namespace Homestead\Report\InternationalReservedBeds;

use \Homestead\ReportHtmlView;
use \Homestead\Term;

class InternationalReservedBedsHtmlView extends ReportHtmlView
{

    protected function render()
    {
        parent::render();

        $this->tpl['TERM'] = Term::toString($this->report->getTerm());

        $this->tpl['TOTAL'] = $this->report->getTotal();

        $myTpl = new \PHPWS_Template('hms');
        $myTpl->setFile('admin/reports/IntlReservedBeds.tpl');

        $myTpl->setData($this->tpl);

        $myTpl->setCurrentBlock('halls');

        $halls = $this->report->getData();

        foreach ($halls as $hall)
        {
            $beds = $hall['beds'];
            foreach ($beds as $bed)
            {
                $myTpl->setCurrentBlock('beds');
                $myTpl->setData($bed);
                $myTpl->parseCurrentBlock();
            }
            $myTpl->setCurrentBlock('halls');
            $myTpl->setData(array('HALL_NAME' => $hall['HALL_NAME'], 'HALL_TOTAL' => $hall['HALL_TOTAL']));
            $myTpl->parseCurrentBlock();
        }

        return $myTpl->get();
    }

}
