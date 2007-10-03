<?php
/**
 * The HMS_Entry_Term class
 * A utility class for handling entry term data items from banner
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class HMS_Entry_Term{


    function get_entry_term($username)
    {
        PHPWS_Core::initModClass('hms','HMS_SOAP.php');
        $entry_term = HMS_SOAP::get_entry_term($username);

        return substr($entry_term, 4, 2);
    }

    function get_entry_year($username)
    {
        PHPWS_Core::initModClass('hms','HMS_SOAP.php');
        $entry_term = HMS_SOAP::get_entry_term($username);

        return substr($entryr_term, 0, 4);
    }

    /**
     * Takes an entry term in the form 'yyyytt' and
     * returns an array with keys 'year' and 'term' 
     * holding a string description on the term given.
     * 
     * If $concat parameter is TRUE, the function concatinates the 
     * array values and returns a single string.
     */
    function entry_term_to_text($entry_term, $concat = FALSE)
    {
        $result = array();
        
        # Grab the year from the entry_term
        $result['year'] = substr($entry_term, 0, 4);

        # Grab the term from the entry_term
        $term = substr($entry_term, 4, 2);
        
        if($term == TERM_SPRING){
            $result['term'] = SPRING;
        }else if($term == TERM_SUMMER1){
            $result['term'] = SUMMER1;
        }else if($term == TERM_SUMMER2){
            $result['term'] = SUMMER2;
        }else if($term == TERM_FALL){
            $result['term'] = FALL;
        }else{
            #TODO: handle a bad term
        }

        if($concat){
            return $result['term'] . ' ' . $result['year'];
        }else{
            return $result;
        }
    }

    /**
     * Takes a year string and a TERM_* define from above
     * returns an integer in the 'yyyytt' format used in banner.
     */
    function text_to_entry_term($year, $term)
    {
        return $year . $term;
    }

    /**
     * Returns an array of the list of terms. Useful for constructing 
     * drop down menus. Array is keyed using the TERM_* defines.
     */
    function get_term_list(){
        $terms = array();

        $terms[TERM_SPRING]  = "Spring";
        $terms[TERM_SUMMER1] = "Summer 1";
        $terms[TERM_SUMEMR2] = "Summer 2";
        $terms[TERM_FALL]    = "Fall";

        return $terms;
    }

}
?>
