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
        return HMS_SOAP::get_application_term($username);
    }

    function get_entry_semester($username)
    {
        PHPWS_Core::initModClass('hms','HMS_SOAP.php');
        $entry_term = HMS_SOAP::get_application_term($username);

        return substr($entry_term, 4, 2);
    }

    function get_entry_year($username)
    {
        PHPWS_Core::initModClass('hms','HMS_SOAP.php');
        $entry_term = HMS_SOAP::get_application_term($username);

        return substr($entryr_term, 0, 4);
    }
}
?>
