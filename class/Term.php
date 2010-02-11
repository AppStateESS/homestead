<?php

/**
 * HMS Term
 * Maintains the "current" term, "active" term, and handles tasks related
 * to creating new terms.
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
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

    public function __construct($term = NULL)
    {
        if(is_null($term)) {
            return;
        }

        $this->term = $term;
        $this->init();
    }

    public function init()
    {
        $db = new PHPWS_DB('hms_term');
        $db->addWhere('term', $this->term);
        $result = $db->loadObject($this);

        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->__toString());
        }
    }

    public function save()
    {
        $db = new PHPWS_DB('hms_term');
        //$db->addWhere('term', $this->term);
        $result = $db->saveObject($this);
        
        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->__toString());
        }
    }

    public function getTerm(){
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

    public function getPdfTerms()
    {
        if(is_null($this->pdf_terms) || empty($this->pdf_terms)){
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
            throw new InvalidConfigurationException('No pdf contract file uploaded for ' . $this->term);
        }

        return $this->pdf_terms;
    }

    public function setPdfTerms($pdf_terms)
    {
        $this->pdf_terms = $pdf_terms;
    }

    public function getTxtTerms()
    {
        if(is_null($this->txt_terms) || empty($this->txt_terms)){
            PHPWS_Core::initModClass('hms', 'exception/InvalidConfigurationException.php');
            throw new InvalidConfigurationException('No text contract file uploaded for ' . $this->term);
        }

        return $this->txt_terms;
    }

    public function setTxtTerms($txt_terms)
    {
        $this->txt_terms = $txt_terms;
    }

    public function getQueueCount()
    {
        $db = new PHPWS_DB('hms_banner_queue');
        $db->addWhere('term', $this->term);
        $result = $db->count();

        if(PHPWS_Error::logIfError($result)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->__toString());
        }

        return $result;
    }

    public function toString($term = NULL, $concat = TRUE)
    {
        if(is_null($term)) {
            $term = $this->term;
        }

        # Grab the year from the entry_term
        $result['year'] = Term::getTermYear($term);

        # Grab the term from the entry_term
        $sem = Term::getTermSem($term);

        if($sem == TERM_SPRING){
            $result['term'] = SPRING;
        }else if($sem == TERM_SUMMER1){
            $result['term'] = SUMMER1;
        }else if($sem == TERM_SUMMER2){
            $result['term'] = SUMMER2;
        }else if($sem == TERM_FALL){
            $result['term'] = FALL;
        }else{
            PHPWS_Core::initModClass('hms','exception/InvalidTermException.php');
            throw new InvalidTermException("Bad term: $term");
        }

        if($concat){
            return $result['term'] . ' ' . $result['year'];
        }else{
            return $result;
        }
    }

    /*************************
     * Static helper methods *
     *************************/

    public static function getCurrentTerm()
    {
        return PHPWS_Settings::get('hms','current_term');
    }

    public static function setCurrentTerm($term)
    {
        PHPWS_Settings::set('hms','current_term',$term);
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
        # Grab the year
        $year = substr($term, 0, 4);

        # Grab the term
        $sem = substr($term, 4, 2);

        if($sem == TERM_FALL){
            return ($year + 1) . "10";
        }else{
            return "$year" . ($sem + 10);
        }
    }

    /**
     * Returns a list of all the terms currently available. Useful for making drop down boxes.
     * @return Array Associate array of terms and their textual representations.
     */
    public static function getTerms()
    {
        $db = new PHPWS_DB('hms_term');
        $db->addOrder('term desc');
        $result = $db->getObjects('Term');

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($result->toString());
        }

        return $result;
    }

    /**
     * Checks a term to see if it really exists in the database.
     * @return boolean True if it exists, False if it doesn't
     */
    public static function isValidTerm($term)
    {
        $db = new PHPWS_DB('hms_term');
        $result = $db->select('col');

        if(PHPWS_Error::logIfError($result)){
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
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

        $terms = self::getTermsAssoc(TRUE);

        $current = self::getCurrentTerm();
        $terms[$current] .= ' (Current)';

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
