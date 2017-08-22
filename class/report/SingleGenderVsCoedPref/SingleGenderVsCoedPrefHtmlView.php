<?php

namespace Homestead\report\SingleGenderVsCoedPref;

class SingleGenderVsCoedPrefHtmlView extends ReportHtmlView {

    protected function render()
    {
        parent::render();
        $this->tpl['TERM'] = Term::toString($this->report->getTerm());


        $this->tpl['MALE_SINGLE_GENDER']   = $this->report->getMaleSingle();
        $this->tpl['MALE_COED']            = $this->report->getMaleCoed();

        $this->tpl['FEMALE_SINGLE_GENDER']   = $this->report->getFemaleSingle();
        $this->tpl['FEMALE_COED']            = $this->report->getFemaleCoed();

        return \PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/SingleGenderVsCoedPref.tpl');
    }
}
