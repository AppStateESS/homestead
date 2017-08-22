<?php

namespace Homestead\report\TwentyFive;

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

class TwentyFiveHtmlView extends ReportHtmlView {

    protected function render()
    {
        parent::render();

        $rows = $this->report->getRows();

        $this->tpl['rows'] = $rows;

        $this->tpl['TERM'] = Term::toString($this->report->getTerm());

        return \PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/TwentyFive.tpl');
    }

}
