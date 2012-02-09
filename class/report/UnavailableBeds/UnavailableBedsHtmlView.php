<?php

class UnavailableBedsHtmlView extends ReportHtmlView {

    protected function render()
    {
        parent::render();

        $this->tpl['TERM'] = Term::toString($this->report->getTerm));

        
        $unavailableBeds = size($this->report->getUnavailableBeds());


        $totalCount = $this->report->getTotalBedCount();
        $unavailableCount = size($unavailableBeds);
        $availableCount = $totalBeds - $unavailableCount;

        $this->tpl['TOTAL_BEDS'] = $totalCount;
        $this->tpl['UNAVAILABLE_BEDS'] = $unavailableCount;
        $this->tpl['AVAILABLE_BEDS'] = $availableCount;

        // foreach beds, rende a row in a table
        foreach($unavailableBeds as $bed){
            // Check for and count special attributes

            // Output row
        }

        return PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/UnavailableBeds.tpl');
    }
}

?>
