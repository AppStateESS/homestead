<?php

/**
 * HMS Utility class for various public functions that don't fit anywhere else
     * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

/************************
 * Date & Time Functions *
************************/

class HMS_Util{

    /**
     * Returns an array where the keys are numeric 1-12, values are text month names
     */
    public static function get_months()
    {
        $months = array('1'=>'January',
                '2'=>'February',
                '3'=>'March',
                '4'=>'April',
                '5'=>'May',
                '6'=>'June',
                '7'=>'July',
                '8'=>'August',
                '9'=>'September',
                '10'=>'October',
                '11'=>'November',
                '12'=>'December');

        return $months;
    }

    /**
     * Returns an array of days of of the month (1-31), keys and values match.
     */
    public static function get_days()
    {
        for($d = 1; $d <= 31; $d++) {
            $days[$d] = $d;
        }

        return $days;
    }

    /**
     * Returns an array of the current year and the next year. Keys and values match.
     */
    public static function get_years_2yr(){
        return array(date('Y')=>date('Y'), date('Y') + 1=>date('Y') + 1);
    }

    /**
     * Returns an array of hours 12 hour format, indexed in 24 hour
     */
    public static function get_hours(){
        $hours = array();

        $hours[0] = '12 AM';

        for($i=1; $i < 24; $i++){
            $hours[$i] = $i;

            if($i == 12){
                $hours[12] = "12 PM";
                continue;
            }

            if($i >= 12){
                $hours[$i] = $i-12 . ' PM';
            }else{
                $hours[$i] = "$i AM";
            }
        }

        return $hours;
    }

    /**
     * Return a date in the format dd-mm-yy given a timestamp
     *
     * @param int $timestamp
     */
    public static function get_short_date($timestamp) {
        if(!isset($timestamp))
            $timestamp = mktime();
         
        return date('j-n-y', $timestamp);
    }

    /**
     * Return a date in long format mm/dd/yyyy given a timestamp
     *
     * @param int $timestamp
     */
    public static function get_long_date($timestamp) {
        if(!isset($timestamp))
            $timestamp = mktime();

        return date('n/j/Y', $timestamp);
    }

    /**
     * Return a date in super long format eg. 7th-November-2007
     *
     * @param int $timestamp
     */
    public static function get_super_long_date($timestamp) {
        if(!isset($timestamp))
            $timestamp = mktime();

        return date('jS-M-Y', $timestamp);
    }

    public static function get_short_date_time($timestamp)
    {
        if(!isset($timestamp)){
            $timestamp = mktime();
        }

        return date('m/d/y h:ia',$timestamp);
    }

    /**
     * Returns a date and time in the long format eg. November 7th, 2007 3:00 PM
     *
     * @param int $timestamp
     */
    public static function get_long_date_time($timestamp)
    {
        if(!isset($timestamp)){
            $timestamp = mktime();
        }

        return date('M jS, Y g:i A', $timestamp);
    }

    public static function getFriendlyDate($timestamp)
    {
        if(!isset($timestamp)){
            $timestamp = mktime();
        }
         
        return date('M jS, Y', $timestamp);
    }

    public static function getPrettyDateRange($startDate, $endDate)
    {
        $avail = "";

        if(!empty($startDate)){
            $avail .= HMS_Util::getFriendlyDate($startDate);
        } else {
            $avail .= "...";
        }

        $avail .= " - ";

        if(!empty($endDate)){
            $avail .= HMS_Util::getFriendlyDate($endDate);
        } else {
            $avail .= "...";
        }

        return $avail;

    }

    public static function relativeTime($time, $now = NULL)
    {
        $time = (int) $time;
        $curr = !is_null($now) ? $now : time();
        $shift = $curr - $time;

        if ($shift < 45){
            $diff = $shift;
            $term = "second";
        }elseif ($shift < 2700){
            $diff = round($shift / 60);
            $term = "minute";
        }elseif ($shift < 64800){
            $diff = round($shift / 60 / 60);
            $term = "hour";
        }else{
            $diff = round($shift / 60 / 60 / 24);
            $term = "day";
        }

        if ($diff > 1){
            $term .= "s";
        }

        return "$diff $term ago";
    }

    /**
     * Determines which color the title bar should be based on
     * the selected and current terms.
     */
    public static function get_title_class(){
        $selected_term = Term::getSelectedTerm();
        $current_term = Term::getCurrentTerm();

        if($selected_term < $current_term){
            return "box-title-red";
        }else if($selected_term == $current_term){
            return "box-title-green";
        }else if($selected_term > $current_term){
            return "box-title-blue";
        }else{
            return "box-title";
        }
    }

    public static function formatGender($gender)
    {
        switch ($gender) {
            case FEMALE:
                return FEMALE_DESC;
            case MALE:
                return MALE_DESC;
            case COED:
                return COED_DESC;
            case AUTO:
                return AUTO_DESC;
            default:
                return 'Error: Unknown gender';
        }
    }

    public static function formatClass($class)
    {
        switch($class){
            case CLASS_FRESHMEN:
                return 'Freshmen';
            case CLASS_SOPHOMORE:
                return 'Sophomore';
            case CLASS_JUNIOR:
                return 'Junior';
            case CLASS_SENIOR:
                return 'Senior';
            default:
                return 'Unknown';
        }
    }

    public static function formatType($type)
    {
        switch($type){
            case TYPE_FRESHMEN:
                return 'New freshmen';
            case TYPE_TRANSFER:
                return 'Transfer';
            case TYPE_CONTINUING:
                return 'Continuing';
            case TYPE_NONDEGREE:
                return 'Non-degree';
            case TYPE_RETURNING:
                return 'Returning';
            case TYPE_READMIT:
                return 'Re-admit';
            default:
                return "Unrecognized: $type";
        }
    }

    public static function formatMealOption($meal)
    {
        if(is_null($meal)){
            return 'Unknown';
        }

        switch($meal){
            case BANNER_MEAL_NONE:
                return 'None';
            case BANNER_MEAL_LOW:
                return 'Low';
            case BANNER_MEAL_STD:
                return 'Standard';
            case BANNER_MEAL_HIGH:
                return 'High';
            case BANNER_MEAL_SUPER:
                return 'Super';
                // 4 Week Meal Plan Removed according to ticket #709
                //case BANNER_MEAL_4WEEK:
                //    return 'Summer (4 weeks)';
                case BANNER_MEAL_5WEEK:
                    return 'Summer (5 weeks)';
                default:
                    return 'Unknown';
        }
    }

    public static function formatCellPhone($number){
        $result = "";

        if(strlen($number) == 10){
            $result  = '('.substr($number, 0, 3).')';
            $result .= substr($number, 3, 3);
            $result .= '-'.substr($number, 6, 4);
        }

        return $result;
    }

    /**
     * Formats a lifestyle preference code into a human-readable string
     *
     * @param int $lifestyleOption
     * @return String
     */
    public static function formatLifestyle($lifestyleOption)
    {
        switch($lifestyleOption){
            case 1:
                return 'Single Gender';
            case 2:
                return 'Co-ed';
            default:
                return 'Unknown';
        }
    }

    /**
     * Formats a bedtime code into a human-readable string
     *
     * @param int $bedtime
     * @return String
     */
    public static function formatBedtime($bedtime){
        switch($bedtime){
            case 1:
                return 'Early';
            case 2:
                return 'Late';
            default:
                return 'Unknown';
        }
    }

    /**
     * Formats a room cleanliness code into a human-readable string
     *
     * @param int $clean
     * @return String
     */
    public function formatRoomCondition($clean){
        switch($clean){
            case 1:
                return 'Clean';
            case 2:
                return 'Messy';
            default:
                return 'Unknown';
        }
    }

    // when fed a number, adds the English ordinal suffix. Works for any
    // number, even negatives
    public static function ordinal($number) {
        if ($number % 100 > 10 && $number %100 < 14){
            $suffix = "th";
        }else{
            switch($number % 10) {

                case 0:
                    $suffix = "th";
                    break;

                case 1:
                    $suffix = "st";
                    break;

                case 2:
                    $suffix = "nd";
                    break;

                case 3:
                    $suffix = "rd";
                    break;

                default:
                    $suffix = "th";
                    break;
            }
        }

        return "${number}<SUP>$suffix</SUP>";
}
}
?>
