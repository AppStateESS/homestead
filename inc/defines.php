<?php

// This file contains the testing defines.  It has been moved out of defines.php
// due to the obvious issue of what to do every time prod is exported.  This
// file is in subversion is inc/hms_defines.php, and should live in phpWebSite's
// root as /inc/hms_defines.php.
require_once(PHPWS_SOURCE_DIR . 'inc/hms_defines.php');

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
 * Student Classes
 */
define('CLASS_FESHMEN',     'FR');
define('CLASS_SOPHOMORE',   'SO');
define('CLASS_JUNIOR',      'JR');
define('CLASS_SENIOR',      'SR');

/**
 * Student Types
 */
define('TYPE_FRESHMEN',     'F');
define('TYPE_TRANSFER',     'T');
define('TYPE_CONTINUING',   'C');

/**
 * Meal Plans
 */
define('HMS_MEAL_LOW',      0);
define('HMS_MEAL_STD',      1);
define('HMS_MEAL_HIGH',     2);
define('HMS_MEAL_SUPER',    3);
define('HMS_MEAL_NONE',     4);

// WTF??!? Banner sucks!
define('BANNER_MEAL_LOW',   2);
define('BANNER_MEAL_STD',   1);
define('BANNER_MEAL_HIGH',  0);
define('BANNER_MEAL_SUPER', 8);

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
 * Activities (used for logging)
 */
define('ACTIVITY_LOGIN',                        0);

define('ACTIVITY_AGREED_TO_TERMS',              1);
define('ACTIVITY_SUBMITTED_APPLICATION',        2);

define('ACTIVITY_SUBMITTED_RLC_APPLICATION',    3);
define('ACTIVITY_ACCEPTED_TO_RLC',              4);

define('ACTIVITY_TOO_OLD_REDIRECTED',           5);

define('ACTIVITY_REQUESTED_AS_ROOMMATE',        6);
define('ACTIVITY_REJECTED_AS_ROOMMATE',         7);
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

/** 
 * Errors 
 */
define('E_SUCCESS', 0); // Everything is fine, nothing is broken.

define('E_ROOMMATE_MALFORMED_USERNAME', 1); // Bad characters, null, etc
define('E_ROOMMATE_REQUESTED_SELF',     3); // You can't request yourself as a roommate
define('E_ROOMMATE_ALREADY_CONFIRMED',  4); // Requestor already has a confirmed roommate (shouldn't get here)
define('E_ROOMMATE_ALREADY_REQUESTED',  5); // This probably means that they are trying to request each other
define('E_ROOMMATE_PENDING_REQUEST',    6); // Requestor already has an unconfirmed roommate request
define('E_ROOMMATE_USER_NOINFO',        7); // Requestee does not seem to be in Banner
define('E_ROOMMATE_GENDER_MISMATCH',    8); // We don't room cats with dogs unless surgery is involved
define('E_ROOMMATE_NO_APPLICATION',     9); // Requestee has no application on file
define('E_ROOMMATE_TYPE_MISMATCH',     10); // In the fall, type F/T and C cannot live together
define('E_ROOMMATE_RLC_ASSIGNMENT',    11); // If requestor is assigned to a different RLC, STOP
define('E_ROOMMATE_RLC_APPLICATION',   12); // If requestor applied for a different RLC, remove their application

?>
