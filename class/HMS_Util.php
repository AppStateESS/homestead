<?php

/**
 * HMS Utility class for various functions that don't fit anywhere else
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

/************************
* Date & Time Functions *
************************/

class HMS_Util{

    /**
     * Returns an array where the keys are numeric 1-12, values are text month names
     */
    function get_months()
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
    function get_days()
    {
        for($d = 1; $d <= 31; $d++) {
            $days[$d] = $d;
        }
        
        return $days;
    }

    /**
     * Returns an array of the current year and the next year. Keys and values match.
     */
    function get_years_2yr(){
        return array(date('Y')=>date('Y'), date('Y') + 1=>date('Y') + 1);
    }

    /**
     * Return a date in the format dd-mm-yy given a timestamp
     *
     * @param int $timestamp
     */
    function get_short_date($timestamp) {
        if(!isset($timestamp))
            $timestamp = mktime();
     
        return date("j-n-y");
    }

    /**
     * Return a date in long format dd-mm-yyyy given a timestamp
     *
     * @param int $timestamp
     */
    function get_long_date($timestamp) {
        if(!isset($timestamp))
            $timestamp = mktime();

        return date("j-n-Y");
    }

    /**
     * Return a date in super long format eg. 7th-November-2007
     *
     * @param int $timestamp
     */
    function get_super_long_date($timestamp) {
        if(!isset($timestamp))
            $timestamp = mktime();

        return date("jS-M-Y");
    }
    
    /**
     * Determines which color the title bar should be based on
     * the selected and current terms.
     */
    function get_title_class(){
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');

        $selected_term = HMS_Term::get_selected_term();
        $current_term = HMS_Term::get_current_term();
        
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
}
?>
