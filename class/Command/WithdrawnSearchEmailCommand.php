<?php

namespace Homestead\Command;

use \Homestead\UserStatus;
use \Homestead\Term;
use \Homestead\WithdrawnSearch;
use \Homestead\HMS_Email;
use \Homestead\HMS;

/**
 * Handles running the Withdrawn search process and emailing the results.
 * @package hms
 * @author Jeremy Booker
 *
 */

require_once PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php';

class WithdrawnSearchEmailCommand extends ScheduledPulse
{

    /**
     * Constructor - Sets up some variables needed by the parent class.
     *
     * @param Integer $id - ID of an existing pulse to load
     */
    public function __construct($id = null)
    {
        $this->module = 'hms';
        $this->class_file = 'Command/WithdrawnSearchEmailCommand.php';
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

        UserStatus::wearMask('HMS System');

        // The search is run over all future terms
        $terms = Term::getFutureTerms();

        $text = "";

        foreach($terms as $term) {
            $search = new WithdrawnSearch($term);
            $search->doSearch();
            $text .= "\n\n=========== " . Term::toString($term) . " ===========\n\n";
            $text .= $search->getTextView();
        }

        $text = $search->getTextView();

        HMS_Email::sendWithdrawnSearchOutput($text);

        UserStatus::removeMask();
        HMS::quit();
    }
}
