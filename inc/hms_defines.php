<?php

/**
 * Testing Flag
 * Set to true to use canned data (no SOAP connection
 * will ever be made).
 */
define('SOAP_TEST_FLAG', true);

/**
 * AXP Testing Flag
 * Set to true to allow fake users to login
 * (No actual authentication to AXP)
 */
define('AXP_TEST_FLAG', true);

/* Errors */
ini_set('ERROR_REPORTING', E_ALL);
ini_set('display_errors', 0);

?>
