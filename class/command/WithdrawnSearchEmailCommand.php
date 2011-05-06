<?php

/**
 * Handles running the Withdrawn search process and emailing the results.
 *
 * @author jbooker
 * @package hms
 */

class WithdrawnSearchEmailCommand extends ScheduledPulse {

    /**
     * Constructor - Sets up some variables needed by the parent class.
     * @param Integer $id - ID of an existing pulse to load
     */
    public function __construct($id = NULL)
    {
        $this->module = 'hms';
        $this->class_file = 'command/WithdrawnSearchEmailCommand.php';
        $this->class = 'WithdrawnSearchEmailCommand';

        parent::__construct($id);
    }

    /**
     * Executes this pulse. Does the withdrawn search and emails the results.
     */
    public function execute()
    {
        // Reschedule the next run of this process
        $sp = $this->makeClone();
        $sp->execute_at = strtotime("tomorrow");
        $sp->save();

        // Load some classes
        PHPWS_Core::initModClass('hms', 'Term.php');
        PHPWS_Core::initModClass('hms', 'WithdrawnSearch.php');

        // The search is run over all future terms
        $terms = Term::getFutureTerms();

        $text = "";

        foreach($terms as $term){
            $search = new WithdrawnSearch($term);
            $search->doSearch();
            $text += "\n\n=========== " . Term::toString($term) . " ===========\n\n";
            $text += $search->getTextView();
        }

        $text = $search->getTextView();

        HMS_Email::sendWithdrawnSearchOutput($text);
        HMS::quit();
    }
}

?>