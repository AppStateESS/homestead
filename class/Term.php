<?php

/**
 * HMS Term
 * Maintains the "current" term, "active" term, and handles tasks related
 * to creating new terms.
 *
 * @package hms
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 *
 */

define('TERM_SPRING',   10);
define('TERM_SUMMER1',  20);
define('TERM_SUMMER2',  30);
define('TERM_FALL',     40);

define('SPRING', 'Spring');
define('SUMMER1', 'Summer 1');
define('SUMMER2', 'Summer 2');
define('FALL', 'Fall');

class Term
{
    public $term;
    public $banner_queue;
    public $pdf_terms;
    public $txt_terms;
    private $isNew = false;

    /**
     * Constructor
     * @param Integer $term Term to load. Can be null to create a new term.
     */
    public function __construct($term = null)
    {
        if(is_null($term)) {
            $this->isNew = true;
            return;
        }

        $this->term = $term;
        $this->init();
    }

    /**
     * Loads this term object, if we have an id.
     */
    public function init()
    {
        $db = new PHPWS_DB('hms_term');
        $db->addWhere('term', $this->term);
        $result = $db->loadObject($this);

        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }
    }

    /**
     * Saves this term object.
     */
    public function save()
    {
        $db = new PHPWS_DB('hms_term');
        // "where" breaks the save if creating new term
        if(!$this->isNew) {
            $db->addWhere('term', $this->term);
        }
        $result = $db->saveObject($this);

        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }
    }

    public function getTerm()
    {
        return $this->term;
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getBannerQueue()
    {
        return $this->banner_queue;
    }

    public function setBannerQueue($flag)
    {
        $this->banner_queue = $flag;
    }

    /**
     * Gets the path of the PDF file for the housing contract for this term.
     * @return String path of housing contract PDF file
     * @throws InvalidConfigurationException
     */
    public function getPdfTerms()
    {
        if(is_null($this->pdf_terms) || empty($this->pdf_terms)) {
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
            throw new InvalidConfigurationException('No pdf contract file uploaded for ' . $this->term);
        }

        if(!file_exists($this->pdf_terms)) {
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
            throw new InvalidConfigurationException('The specified pdf contract file uploaded for ' . $this->term . ' does not exist.');
        }

        return $this->pdf_terms;
    }

    public function setPdfTerms($pdf_terms)
    {
        $this->pdf_terms = $pdf_terms;
    }

    /**
     * Gets the path of the text file for the housing contract for this term.
     * @return String Path of the housing contract txt file.
     * @throws InvalidConfigurationException
     */
    public function getTxtTerms()
    {
        if(is_null($this->txt_terms) || empty($this->txt_terms)) {
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
            throw new InvalidConfigurationException('No text contract file uploaded for ' . $this->term);
        }

        if(!file_exists($this->txt_terms)) {
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
            throw new InvalidConfigurationException('The specified text contract file uploaded for ' . $this->term . ' does not exist.');
        }

        return $this->txt_terms;
    }

    public function setTxtTerms($txt_terms)
    {
        $this->txt_terms = $txt_terms;
    }

    /**
     * Returns the number of items in the Banner queue for this term
     * TODO: Move this to the BannerQueue class.
     * @return Integer The number of items in the banner queue.
     */
    public function getQueueCount()
    {
        $db = new PHPWS_DB('hms_banner_queue');
        $db->addWhere('term', $this->term);
        $result = $db->count();

        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->__toString());
        }

        return $result;
    }

    /*************************
     * Static helper methods *
     *************************/

    /**
     * Returns a string representation of the integer form of a term.
     * @param Integer $term
     * @param Boolean $concat Whether or not to concatenate the year and term together (can return a array instead).
     * @throws InvalidTermException
     */
    public static function toString($term, $concat = true)
    {
        // Grab the year from the entry_term
        $result['year'] = Term::getTermYear($term);

        // Grab the term from the entry_term
        $sem = Term::getTermSem($term);

        if($sem == TERM_SPRING) {
            $result['term'] = SPRING;
        } else if($sem == TERM_SUMMER1) {
            $result['term'] = SUMMER1;
        } else if($sem == TERM_SUMMER2) {
            $result['term'] = SUMMER2;
        } else if($sem == TERM_FALL) {
            $result['term'] = FALL;
        } else {
            PHPWS_Core::initModClass('hms', 'exception/InvalidTermException.php');
            throw new InvalidTermException("Bad term: $term");
        }

        if($concat) {
            return $result['term'] . ' ' . $result['year'];
        } else {
            return $result;
        }
    }

    public static function getCurrentTerm()
    {
        return PHPWS_Settings::get('hms', 'current_term');
    }

    public static function setCurrentTerm($term)
    {
        PHPWS_Settings::set('hms', 'current_term', $term);
        PHPWS_Settings::save('hms');

        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        $username = Current_User::getUsername();
        HMS_Activity_Log::log_activity($username, ACTIVITY_CHANGE_ACTIVE_TERM, $username, "Active term set by $username to $term");
    }

    public static function getPrintableCurrentTerm()
    {
        return self::toString(self::getCurrentTerm());
    }

    public static function getSelectedTerm()
    {
        if(isset($_SESSION['selected_term'])) {
            return $_SESSION['selected_term'];
        } else {
            return self::getCurrentTerm();
        }
    }

    public static function setSelectedTerm($term)
    {
        $_SESSION['selected_term'] = $term;
        return;
    }

    public static function getPrintableSelectedTerm()
    {
        return self::toString(self::getSelectedTerm());
    }

    public static function isCurrentTermSelected()
    {
        return self::getSelectedTerm() == self::getCurrentTerm();
    }

    public static function getTermYear($term)
    {
        return substr($term, 0, 4);
    }

    public static function getTermSem($term)
    {
        return substr($term, 4, 2);
    }

    public static function getNextTerm($term)
    {
        // Grab the year
        $year = substr($term, 0, 4);

        // Grab the term
        $sem = substr($term, 4, 2);

        if($sem == TERM_FALL) {
            return ($year + 1) . "10";
        } else {
            return "$year" . ($sem + 10);
        }
    }

    public static function getPrevTerm($term)
    {
        // Grab the year
        $year = substr($term, 0, 4);

        // Grab the term
        $sem = substr($term, 4, 2);

        if($sem == TERM_SPRING) {
            return ($year - 1) . "40";
        } else {
            return "$year" . ($sem - 10);
        }
    }

    /**
     * Returns a list of all the terms currently available. Useful for making drop down boxes.
     *
     * @return Array Associate array of terms and their textual representations.
     */
    public static function getTerms()
    {
        $db = new PHPWS_DB('hms_term');
        $db->addOrder('term desc');
        $result = $db->getObjects('Term');

        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    /**
     * Returns a simple array of term (in numeric form) of all terms after the 'current' term.
     * @return Array Returns an array of all the terms after the current term.
     */
    public function getFutureTerms()
    {
        $objs = self::getTerms();
        $currTerm = self::getCurrentTerm();

        $terms = array();

        foreach($objs as $t) {
            if($t->term > $currTerm) {
                $terms[] = $t->term;
            }
        }

        return $terms;
    }

    /**
     * Checks a term to see if it really exists in the database.
     *
     * @return boolean True if the term exists, False if it doesn't
     */
    public static function isValidTerm($term)
    {
        $db = new PHPWS_DB('hms_term');
        $result = $db->select('col');

        if(PHPWS_Error::logIfError($result)) {
            throw new DatabaseException($result->toString());
        }

        return in_array($term, $result);
    }

    public static function validateTerm($term)
    {
        if(!self::isValidTerm($term)) {
            PHPWS_Core::initModClass('hms', 'exception/InvalidTermException.php');
            throw new InvalidTermException("$term is not a valid term.");
        }
    }

    public static function getTermsAssoc()
    {
        $objs = self::getTerms();

        $terms = array();

        foreach($objs as $term) {
            $t = $term->term;
            $terms[$t] = Term::toString($t);
        }

        return $terms;
    }

    public static function getTermSelector()
    {
        if(UserStatus::isGuest()) {
            return dgettext('hms', 'Housing Management System');
        }

        $terms = self::getTermsAssoc(true);

        $current = self::getCurrentTerm();
        if(isset($terms[$current])){
        	$terms[$current] .= ' (Current)';
        }

        $form = new PHPWS_Form('term_selector');

        $cmd = CommandFactory::getCommand('SelectTerm');
        $cmd->initForm($form);

        $form->addDropBox('term', $terms);
        $form->setMatch('term', self::getSelectedTerm());

        $tags = $form->getTemplate();
        javascript('modules/hms/SelectTerm');
        return PHPWS_Template::process($tags, 'hms', 'admin/SelectTerm.tpl');
    }

    /**
     * Returns an array of the list of semesters. Useful for constructing
     * drop down menus. Array is keyed using the TERM_* defines.
     *
     * @return Array An array of possible semesters ("terms").
     */
    public static function getSemesterList()
    {
        $terms = array();

        $terms[TERM_SPRING]  = SPRING;
        $terms[TERM_SUMMER1] = SUMMER1;
        $terms[TERM_SUMMER2] = SUMMER2;
        $terms[TERM_FALL]    = FALL;

        return $terms;
    }
}

?>
