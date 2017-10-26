<?php

namespace Homestead;

/**
 * iCsvReport Interface
 * Enforces the methods necessary for the ReportCsvView to retrieve the CSV data from the implementing report class.
 *
 * @author jbooker
 * @package HMS
 */
interface iCsvReport {
    /**
     * Returns an array of column names, used to make the csv file header line
     */
    public function getCsvColumnsArray();

    /**
     * Returns a two-dimensional array of data rows, each containing the columns values for that row
     */
    public function getCsvRowsArray();
}
