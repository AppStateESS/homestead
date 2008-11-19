<?php

/**************
 * Login Link *
 **************/
define('LOGIN_TEST_FLAG', 'false'); 
//define('HMS_LOGIN_LINK', 'index.php?module=hms&type=student&op=fake_login');
define('HMS_LOGIN_LINK', '/login');

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
 * Email testing flag
 * Set to true to prevent email from actually being sent.
 * Instead, it will be logged to a text file.
 */
define('EMAIL_TEST_FLAG', true);

/* Errors */
ini_set('ERROR_REPORTING', E_ALL);
ini_set('display_errors', 1);

/* Memory limit */
ini_set('memory_limit', '512M');
?>
