<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('hms', 'RoommateException');

class RoommateCompatibilityException extends RoommateException
{
    public function __construct($code)
    {
        parent::__construct(self::resolveCode($code), $code);
    }

    public static function resolveCode($code)
    {
        switch($code) {
            case E_ROOMMATE_MALFORMED_USERNAME:
                return "Malformed Username.";

            case E_ROOMMATE_REQUESTED_SELF:
                return "You cannot request yourself.";

            case E_ROOMMATE_ALREADY_CONFIRMED:
                return "You already have a confirmed roommate.";

            case E_ROOMMATE_REQUESTED_CONFIRMED:
                return "The roommate you requested already has a confirmed roommate.";

            case E_ROOMMATE_ALREADY_REQUESTED:
                return "You already have a pending roommate request.";

            case E_ROOMMATE_PENDING_REQUEST:
                return "You already have an uncomfirmed roommate request.";

            case E_ROOMMATE_USER_NOINFO:
                return "Your requested roommate does not seem to have a student record.  Please be sure you typed the username correctly.";

            case E_ROOMMATE_NO_APPLICATION:
                return "Your requested roommate has not filled out a housing application.";

            case E_ROOMMATE_GENDER_MISMATCH:
                return "Please select a roommate of the same sex as yourself.";

            case E_ROOMMATE_TYPE_MISMATCH:
                return "You can not choose a student of a different type than yourself (i.e. a freshmen student can only request another freshmen student, and not a transfer or continuing student).";

            case E_ROOMMATE_RLC_APPLICATION:
                return "Your roommate request could not be completed because you and/or your requested roommate have applied for different Unique Housing Options.";

            case E_ROOMMATE_RLC_ASSIGNMENT:
                return "Your roommate request could not be completed because you and/or your requested roommate are assigned to a Unique Housing Option, and you are both not a member of the same Unique Housing Option.";

            default:
                return "Unknown Error $result.";
        }
    }
}

?>
