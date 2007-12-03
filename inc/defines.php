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
 * Required Files
 */
define('AXP_LOCATION', 'inc/axp.php');

/**
 * Gender defines
 */
define('FEMALE',    0);
define('MALE',      1);
define('COED',      2);

define('FEMALE_DESC',   'Female');
define('MALE_DESC',     'Male');
define('COED_DESC',     'Coed');

/**
 * Online/Offline Defines
 */
define("ONLINE",    0);
define("OFFLINE",   1);

define("ONLINE_DESC",   'Online');
define("OFFLINE_DESC",  'Offline');

/**
 * Terms
 */
define('TERM_SPRING',   '10');
define('TERM_SUMMER1',  '20');
define('TERM_SUMMER2',  '30');
define('TERM_FALL',     '40');

define('SPRING',    'Spring');
define('SUMMER1',   'Summer 1');
define('SUMMER2',   'Summer 2');
define('FALL',      'Fall');

/**
 * Errors
 */
define('TOOLATE',  -4);
define('TOOOLD',   -3);
define('BADTUPLE', -2);
define('TOOEARLY', -1);

/**
 * User classes
 */
define('STUDENT',   1);
define('ADMIN',     2);
define('BADCLASS',  3);

/**
 * Activities
 */
define('ACTIVITY_LOGIN',                        0);

define('ACTIVITY_AGREED_TO_TERMS',              1);
define('ACTIVITY_SUBMITTED_APPLICATION',        2);

define('ACTIVITY_SUBMITTED_RLC_APPLICATION',    3);
define('ACTIVITY_ACCEPTED_TO_RLC',              4);

define('ACTIVITY_REQUESTED_ROOMMATE',           5);
define('ACTIVITY_REQUESTED_AS_ROOMMATE',        6);
define('ACTIVITY_ACCEPTED_ROOMMATE',            7);
define('ACTIVITY_ACCEPTED_AS_ROOMMATE',         8);
define('ACTIVITY_PROFILE_CREATED',              9);

define('ACTIVITY_ASSIGNED',                     10);
define('ACTIVITY_AUTO_ASSIGNED',                11);
define('ACTIVITY_REMOVED',                      12);
define('ACTIVITY_ASSIGNMENT_REPORTED',          13);
define('ACTIVITY_REMOVAL_REPORTED',             14);
define('ACTIVITY_LETTER_PRINTED',               15);

define('ACTIVITY_BANNER_ERROR',                 16);

define('HMS_MULTIPLE_ASSIGNMENTS',              17);
?>
