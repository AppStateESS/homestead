<?php

/**
 * UI/Banner_Queue.php
 * User Interface for the Banner Queue
 * 
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

class HMS_Banner_Queue_UI
{
    /**
     * Enables the Banner Queue for the given term.
     */
    function enable()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term.php');
        $term = $_REQUEST['term'];
        
        $obj = &new HMS_Term($term);
        $obj->enable_banner_queue();
        $obj->save();

        return HMS_Term::show_edit_terms('Banner queue enabled for term '.
            HMS_Term::term_to_text($term, true));
    }

    /**
     * Flushes the Banner Queue for the given term and then marks it disabled.
     */
    function disable()
    {
        $term = $_REQUEST['term'];

        PHPWS_Core::initModClass('hms', 'HMS_Banner_Queue.php');
        $result = HMS_Banner_Queue::processAll($term);

        if($result === TRUE) {
            PHPWS_Core::initModClass('hms', 'HMS_Term.php');

            $obj = &new HMS_Term($term);
            $obj->disable_banner_queue();
            $obj->save();

            return HMS_Term::show_edit_terms('Banner queue disabled for term '.
                HMS_Term::term_to_text($term, true));
        }

        $content = "<h1>There were errors pushing to Banner.</h1>\n<ul>\n";

        foreach($result as $error) {
            $content .= "<li>{$error['username']}: {$error['code']}</li>\n";
        }

        $content .= "</ul>\n";

        Layout::nakedDisplay($content, NULL, TRUE);

        return;
    }

    /**
     * Handles main UI functionality
     */
    function main()
    {
        if(!Current_User::allow('hms', 'banner_queue')) {
            return PHPWS_Template::process(array(), 'hms',
                'admin/permission_denied.tpl');
        }

        switch($_REQUEST['op']) {
            case 'enable':
                return HMS_Banner_Queue_UI::enable();
            case 'disable':
                return HMS_Banner_Queue_UI::disable();
            default:
        }
    }
}

?>
