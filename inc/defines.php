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

/**
 * Gender defines
 */
define("FEMALE", 0);
define("MALE",1);

/**
 * Terms
 */
define("TERM_SPRING",  '10');
define("TERM_SUMMER1", '20');
define("TERM_SUMMER2", '30');
define("TERM_FALL",    '40');

define("SPRING",  "Spring");
define("SUMMER1", "Summer 1");
define("SUMMER2", "Summer 2");
define("FALL",    "Fall");

/**
 * Errors
 */
define("TOOOLD", -3);
define("BADTUPLE", -2);
define("TOOEARLY", -1 );
define("TOOLATE", 0 );

/**
 * User classes
 */
define("STUDENT", 1 );
define("ADMIN", 2 );
define("BADCLASS", 3);

?>
