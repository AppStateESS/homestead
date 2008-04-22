<?php

/**********************
 * SOAP Testing Flags *
 **********************/

 /**
 * SOAP Info test flag
 * Set to true to use canned student info (no SOAP connection
 * will ever be made).
 */
define('SOAP_INFO_TEST_FLAG', true);

/**
 * SOAP Reporting test flag
 * Set to true to prevent applications, assignments, etc.
 * from being reported back to banner.
 */
define('SOAP_REPORT_TEST_FLAG', true);

/**
 * AXP Testing Flag
 * Set to true to allow fake users to login
 * (No actual authentication to AXP)
 */
define('AXP_TEST_FLAG', true);

/* Errors */
ini_set('ERROR_REPORTING', E_ALL);
ini_set('display_errors', 1);

?>
