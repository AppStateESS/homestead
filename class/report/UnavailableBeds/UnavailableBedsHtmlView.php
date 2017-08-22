<?php

namespace Homestead\report\UnavailableBeds;

class UnavailableBedsHtmlView extends ReportHtmlView {

    protected function render()
    {
        parent::render();

        $this->tpl['TERM'] = Term::toString($this->report->getTerm());

        $unavailableBeds = $this->report->getUnavailableBeds();

        $totalCount = $this->report->getTotalBedCount();
        $unavailableCount = count($unavailableBeds);
        $availableCount = $totalCount - $unavailableCount;

        $this->tpl['TOTAL_BEDS']       = $totalCount;
        $this->tpl['UNAVAILABLE_BEDS'] = $unavailableCount;
        $this->tpl['AVAILABLE_BEDS']   = $availableCount;

        $rows = array();

        $reservedTotal   = 0;
        $raTotal         = 0;
        $raRoommateTotal = 0;
        $privateTotal    = 0;
        $overflowTotal   = 0;
        $parlorTotal     = 0;
        $intlTotal       = 0;
        $offlineTotal    = 0;

        // foreach beds, rende a row in a table
        foreach($unavailableBeds as $bed){
            // Check for and count special attributes
            if($bed['reserved'] == 1){
                $reservedTotal++;
            }

            if($bed['ra'] == 1){
                $raTotal++;
            }

            if($bed['ra_roommate'] == 1){
                $raRoommateTotal++;
            }

            if($bed['private'] == 1){
                $privateTotal++;
            }

            if($bed['overflow'] == 1){
                $overflowTotal++;
            }

            if($bed['parlor'] == 1){
                $parlorTotal++;
            }

            if($bed['international_reserved'] == 1){
                $intlTotal++;
            }

            if($bed['offline'] == 1){
                $offlineTotal++;
            }

            // Output row
            $row = array();
            $row['HALL']        = $bed['hall_name'];
            $row['ROOM']        = $bed['room_number'];
            $row['BED_LETTER']  = $bed['bed_letter'];
            $row['RESERVED']    = $bed['reserved'];
            $row['RA']          = $bed['ra'];
            $row['RA_ROOMMATE'] = $bed['ra_roommate'];
            $row['PRIVATE']     = $bed['private'];
            $row['OVERFLOW']    = $bed['overflow'];
            $row['PARLOR']      = $bed['parlor'];
            $row['INTL']        = $bed['international_reserved'];
            $row['OFFLINE']     = $bed['offline'];

            $rows[] = $row;
        }

        $this->tpl['bed_rows'] = $rows;

        $this->tpl['RESERVED_TOTAL']    = $reservedTotal;
        $this->tpl['RA_TOTAL']          = $raTotal;
        $this->tpl['RA_ROOMMATE_TOTAL'] = $raRoommateTotal;
        $this->tpl['PRIVATE_TOTAL']     = $privateTotal;
        $this->tpl['OVERFLOW_TOTAL']    = $overflowTotal;
        $this->tpl['PARLOR_TOTAL']      = $parlorTotal;
        $this->tpl['INTL_TOTAL']        = $intlTotal;
        $this->tpl['OFFLINE_TOTAL']     = $offlineTotal;

        return \PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/UnavailableBeds.tpl');
    }
}
