<?php

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

class SpecialNeedsRequestHtmlView extends ReportHtmlView {
    protected function render()
    {
        $this->tpl = $this->report->getSortedRows();
        parent::render();

        $this->tpl['f_total'] = $this->report->f_total;
        $this->tpl['s_total'] = $this->report->s_total;
        $this->tpl['g_total'] = $this->report->g_total;
        $this->tpl['m_total'] = $this->report->m_total;
        $this->tpl['term'] = Term::toString($this->report->getTerm());

        return PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/SpecialNeedsRequest.tpl');
    }
}

?>
