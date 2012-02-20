<?php

// This file contains the testing defines.  It has been moved out of defines.php
// due to the obvious issue of what to do every time prod is exported.  This
// file is in subversion is inc/hms_defines.php, and should live in phpWebSite's
// root as /inc/hms_defines.php.
require_once(PHPWS_SOURCE_DIR . 'inc/hms_defines.php');

/**
 * Name & Email address info - Used for sending out emails
 */
define('SYSTEM_NAME', 'ASU Housing Management System'); // Used as "from" name in emails
define('EMAIL_ADDRESS', 'uha'); // user name of email account to send email from
define('DOMAIN_NAME', 'appstate.edu'); // domain name to send email from
define('FROM_ADDRESS', EMAIL_ADDRESS . '@' . DOMAIN_NAME); // fully qualified "from" address
define('TO_DOMAIN', '@'. DOMAIN_NAME); // Default domain to send email to, beginning with '@'

/**
 * Online/Offline Defines
 */
define("ONLINE",    0);
define("OFFLINE",   1);

define("ONLINE_DESC",   'Online');
define("OFFLINE_DESC",  'Offline');

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
 * Student Classes
 */
define('CLASS_FRESHMEN',    'FR');
define('CLASS_SOPHOMORE',   'SO');
define('CLASS_JUNIOR',      'JR');
define('CLASS_SENIOR',      'SR');

/**
 * Student Types
 */
define('TYPE_FRESHMEN',     'F');
define('TYPE_TRANSFER',     'T');
define('TYPE_CONTINUING',   'C');
define('TYPE_RETURNING',    'R');
define('TYPE_READMIT',      'Z');
define('TYPE_WITHDRAWN',    'W');
define('TYPE_NONDEGREE',    'N');
define('TYPE_GRADUATE',     'G');

/**
 * Student Levels
 */
define('LEVEL_UNDERGRAD',   'U');
define('LEVEL_GRAD',        'G');
define('LEVEL_DOCTORAL',    'D');
define('LEVEL_SPECIALIST',  'P');
define('LEVEL_UNDECLARED',  '00');
define('LEVEL_GRAD2',       'G2');

/**
 * Meal Plans
 */
//define('HMS_MEAL_LOW',      0);
//define('HMS_MEAL_STD',      1);
//define('HMS_MEAL_HIGH',     2);
//define('HMS_MEAL_SUPER',    3);
//define('HMS_MEAL_NONE',     4);

// WTF??!? Banner sucks!
define('BANNER_MEAL_LOW',   '2');
define('BANNER_MEAL_STD',   '1');
define('BANNER_MEAL_HIGH',  '0');
define('BANNER_MEAL_SUPER', '8');
define('BANNER_MEAL_NONE', '-1');

// Summer Meal plan codes
define('BANNER_MEAL_4WEEK', 'S4');
define('BANNER_MEAL_5WEEK', 'S5');


/**
 * Assignment Types
 */

// Default
define('ASSIGN_ADMIN', 		'admin'); // general administrative

// General Populations
define('ASSIGN_LOTTERY',     'lottery');
define('ASSIGN_FR_AUTO',     'auto_assign'); // auto-assigner
define('ASSIGN_FR',          'freshmen');
define('ASSIGN_TRANSFER', 	 'transfer');

// Sororities
define('ASSIGN_APH', 	'aph');

// RLCs
define('ASSIGN_RLC_FRESHMEN',   'rlc_freshmen');
define('ASSIGN_RLC_TRANSFER',   'rlc_transfer');
define('ASSIGN_RLC_CONTINUING', 'rlc_continuing');

// Honors
define('ASSIGN_HONORS_FRESHMEN',   'honors_freshmen');
define('ASSIGN_HONORS_CONTINUING', 'honors_continuing');

// LLC
define('ASSIGN_LLC_FRESHMEN',   'llc_freshmen');
define('ASSIGN_LLC_CONTINUING', 'llc_continuing');

// International
define('ASSIGN_INTL',       'international');

// RAs
define('ASSIGN_RA',          'ra');
define('ASSIGN_RA_ROOMMATE', 'ra_roommate');

// Special/medical needs needs
define('ASSIGN_MEDICAL', 	'medical');
define('ASSIGN_SPECIAL', 	'special');

// RHA
define('ASSIGN_RHA', 'rha');
define('ASSIGN_SCHOLARS', 'scholars');

// Processes - DEPRECATED
define('ASSIGN_CHANGE', 	'achange'); // For room change requests
define('ASSIGN_COPY',		'acopy'); // For copying from previous term


/**
 * Unassignment Types
 */
define('UNASSIGN_ADMIN',	'uadmin'); // General administrative
define('UNASSIGN_CHANGE',	'uchange'); // For room change requests
define('UNASSIGN_WITHDRAWN','uwithdrawn'); // Withdrawn
define('UNASSIGN_INTENTNORETURN','uintentnoreturn'); // Intent not to return
define('UNASSIGN_REASSIGN', 'ureassign'); // implicit removal for re-assign command
define('UNASSIGN_NOREASON',	'unone');



/**
 * Pretty Names
 */
define('admin', 	'Administrative');

define('lottery', 	'Lottery');
define('auto_assign',		'Auto-Assigned');
define('freshmen',	'Freshmen');
define('transfer', 'Transfer');

define('aph', 'APH');

define('rlc_freshmen', 'RLC Freshmen');
define('rlc_transfer', 'RLC Transfer');
define('rlc_continuing', 'RLC Continuing');

define('honors_freshmen', 'Honors Freshmen');
define('honors_continuing', 'Honors Continuing');

define('llc_freshmen', 'LLC Freshmen');
define('llc_continuing', 'LLC Continuing');

define('international', 'International');

define('ra', 'RA');
define('ra_roommate', 'RA Roommate');


define('medical', 	'Medical');
define('special',	'Special Needs');

define('rha', 'RHA');
define('scholars', 'Plemmons & Diversity');

// Deprecated
define('achange',   'Room Change');
define('acopy',     'Term Rollover');

// Removal Reasons
define('uadmin',	'Administrative');
define('uchange',	'Room Change');
define('uwithdrawn','Withdrawn');
define('uintentnoreturn', 'Intent Not To Return');
define('ureassign',	'Reassignment');
define('unone',		'Not Specified');


/**
 * Address types
 */
define('ADDRESS_PRMT_STUDENT',      'PS'); // Permanent student address
define('ADDRESS_PRMT_RESIDENCE',    'PR'); // permenent student residence

/**
 * Room Types (used for summer applications)
 */
define('ROOM_TYPE_DOUBLE', 0);
define('ROOM_TYPE_PRIVATE', 1);

/**
 * User classes
 */
define('STUDENT',   1);
define('ADMIN',     2);
define('BADCLASS',  3);

/**
 * Queue Types
 */
define('BANNER_QUEUE_ASSIGNMENT', 1);
define('BANNER_QUEUE_REMOVAL',    2);

/**
 * Menu Types
 */
define('MENU_TYPE_ALL',         0);
define('MENU_TYPE_STRUCTURE',   1);
define('MENU_TYPE_RLC',         2);
define('MENU_TYPE_ASSIGNMENT',  3);
define('MENU_TYPE_STATISTICS',  4);
define('MENU_TYPE_SETTINGS',    5);

/**
 * Assignment Features
 */
define('APPLICATION_RLC_APP',          0);
define('APPLICATION_ROOMMATE_PROFILE', 1);
define('APPLICATION_SELECT_ROOMMATE',  2);

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

define('ACTIVITY_LOGIN_AS_STUDENT',             18);

define('ACTIVITY_ADMIN_ASSIGNED_ROOMMATE',      19);
define('ACTIVITY_ADMIN_REMOVED_ROOMMATE',       20);
define('ACTIVITY_AUTO_CANCEL_ROOMMATE_REQ',     21);

// Activities for withdrawn students
define('ACTIVITY_WITHDRAWN_APP',                22);
define('ACTIVITY_WITHDRAWN_ASSIGNMENT_DELETED', 23);
define('ACTIVITY_WITHDRAWN_ROOMMATE_DELETED',   24);
define('ACTIVITY_WITHDRAWN_RLC_APP_DENIED',     25);
define('ACTIVITY_WITHDRAWN_RLC_ASSIGN_DELETED', 26);

define('ACTIVITY_APPLICATION_REPORTED',         27);

define('ACTIVITY_DENIED_RLC_APPLICATION',       28);
define('ACTIVITY_UNDENIED_RLC_APPLICATION',     29);
define('ACTIVITY_ASSIGN_TO_RLC',                30);
define('ACTIVITY_RLC_APP_SUBMITTED',            31);

// Activities for updating a username
define('ACTIVITY_USERNAME_UPDATED',             32);
define('ACTIVITY_APPLICATION_UPDATED',          33);
define('ACTIVITY_RLC_APPLICATION_UPDATED',      34);
define('ACTIVITY_ASSIGNMENTS_UPDATED',          35);
define('ACTIVITY_BANNER_QUEUE_UPDATED',         36);
define('ACTIVITY_ROOMMATES_UPDATED',            37);
define('ACTIVITY_ROOMMATE_REQUESTS_UPDATED',    38);

// Misc.
define('ACTIVITY_CHANGE_ACTIVE_TERM',           39);
define('ACTIVITY_ADD_NOTE',                     40);

// Activities for the lottery
define('ACTIVITY_LOTTERY_SIGNUP_INVITE',        41); //depricated
define('ACTIVITY_LOTTERY_ENTRY',                42);
define('ACTIVITY_LOTTERY_INVITED',              43);
define('ACTIVITY_LOTTERY_REMINDED',             44);
define('ACTIVITY_LOTTERY_ROOM_CHOSEN',          45);
define('ACTIVITY_LOTTERY_REQUESTED_AS_ROOMMATE',46);
define('ACTIVITY_LOTTERY_ROOMMATE_REMINDED',    47);
define('ACTIVITY_LOTTERY_CONFIRMED_ROOMMATE',   48);
define('ACTIVITY_LOTTERY_EXECUTED',             49);

// Term Activities
define('ACTIVITY_CREATE_TERM',                  50);

// Notification activities
define('ACTIVITY_NOTIFICATION_SENT',            51);
define('ACTIVITY_ANON_NOTIFICATION_SENT',       52);
define('ACTIVITY_HALL_NOTIFIED',                53);
define('ACTIVITY_HALL_NOTIFIED_ANONYMOUSLY',    54);

define('ACTIVITY_LOTTERY_OPTOUT',               55);

define('ACTIVITY_STUDENT_BROKE_ROOMMATE',       56);
define('ACTIVITY_STUDENT_CANCELLED_ROOMMATE_REQUEST', 57);

// Room Change activities
define('ACTIVITY_ROOM_CHANGE_SUBMITTED', 58);
define('ACTIVITY_ROOM_CHANGE_APPROVED_RD', 59);
define('ACTIVITY_ROOM_CHANGE_APPROVED_HOUSING', 60);
define('ACTIVITY_ROOM_CHANGE_COMPLETED', 61);
define('ACTIVITY_ROOM_CHANGE_DENIED', 62);

define('ACTIVITY_FLOOR_NOTIFIED_ANONYMOUSLY',   63);
define('ACTIVITY_FLOOR_NOTIFIED',               64);

define('ACTIVITY_LOTTERY_ROOMMATE_DENIED',      65);

define('ACTIVITY_RLC_APPLICATION_DELETED',		66);

/**
 * Errors
 */
define('E_SUCCESS', 0); // Everything is fine, nothing is broken.

define('TOOLATE',  -4);
define('TOOOLD',   -3);
define('BADTUPLE', -2);
define('TOOEARLY', -1);

/**
 * Roommate errors
 */
define('E_ROOMMATE_MALFORMED_USERNAME',  1); // Bad characters, null, etc
define('E_ROOMMATE_REQUESTED_SELF',      3); // You can't request yourself as a roommate
define('E_ROOMMATE_ALREADY_CONFIRMED',   4); // Requestor already has a confirmed roommate (shouldn't get here)
define('E_ROOMMATE_ALREADY_REQUESTED',   5); // This probably means that they are trying to request each other
define('E_ROOMMATE_PENDING_REQUEST',     6); // Requestor already has an unconfirmed roommate request
define('E_ROOMMATE_USER_NOINFO',         7); // Requestee does not seem to be in Banner
define('E_ROOMMATE_GENDER_MISMATCH',     8); // We don't room cats with dogs unless surgery is involved
define('E_ROOMMATE_NO_APPLICATION',      9); // Requestee has no application on file
define('E_ROOMMATE_TYPE_MISMATCH',      10); // In the fall, type F/T and C cannot live together
define('E_ROOMMATE_RLC_ASSIGNMENT',     11); // If requestor is assigned to a different RLC, STOP
define('E_ROOMMATE_RLC_APPLICATION',    12); // If requestor applied for a different RLC, remove their application
define('E_ROOMMATE_REQUESTED_CONFIRMED',13); // If requestee has a confirmed roommate

define('E_PERMISSION_DENIED',   14);

/**
 * Assignment Errors
 */
define('E_ASSIGN_MALFORMED_USERNAME',   15);
define('E_ASSIGN_NULL_HALL_OBJECT',     16);
define('E_ASSIGN_NULL_FLOOR_OBJECT',    17);
define('E_ASSIGN_NULL_ROOM_OBJECT',     18);
define('E_ASSIGN_NULL_BED_OBJECT',      19);
define('E_ASSIGN_ROOM_FULL',            20);
define('E_ASSIGN_GENDER_MISMATCH',      21);
define('E_ASSIGN_BANNER_ERROR',         22);
define('E_ASSIGN_HMS_DB_ERROR',         23);
define('E_ASSIGN_ALREADY_ASSIGNED',     24);
define('E_ASSIGN_NO_DESTINATION',       25);
define('E_ASSIGN_BED_NOT_EMPTY',        26);
define('E_ASSIGN_WITHDRAWN',            27);
define('E_ASSIGN_NO_DATA',              28);
define('E_ASSIGN_ROOM_OFFLINE',         29);

/**
 * Un-assignment Errors
 */
define('E_UNASSIGN_NOT_ASSIGNED',       30);
define('E_UNASSIGN_ASSIGN_LOAD_FAILED', 31);
define('E_UNASSIGN_BANNER_ERROR',       32);
define('E_UNASSIGN_HMS_DB_ERROR',       33);

/**
 * Lottery Errors
 */
define('E_LOTTERY_ROOMMATE_INVITE_NOT_FOUND',   33);
?>
