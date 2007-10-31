<?php
/**
 * The HMS_Term class
 * Handles creating/deleting/modifying terms
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class HMS_Term{

    var $term;

    function HMS_Term($term = NULL)
    {
        if(!isset($term)){
            return;
        }

        $this->set_term($term);

        # Initalize
        # This code (initialization stuff) commented out due to the removal of the 'id' field.
        /*
        $result = $this->init();
        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
            return $result;
        }
        */
    }

    /*
    function init()
    {
        if(!isset($this->term)){
            return FALSE;
        }

        $db = &new PHPWS_DB('hms_term');
        $db->addWhere('term',$this->get_term());
        $result = $db->select('row');

        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
        }

        return $result;
    }
    */

    function save()
    {
        $db = &new PHPWS_DB('hms_term');

        $result = $db->saveObject($this);

        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
        }

        return $result;
    }
    
    /*************
     * Main menu *
     ************/
    
    function main()
    {
        switch($_REQUEST['op'])
        {
            case 'show_edit_terms':
                return HMS_Term::show_edit_terms();
                break;
            case 'create_new_term':
                return HMS_Term::create_new_term();
            default:
                return "Undefined term op";
                break;
        }
    }

    /******************
     * Static Methods *
     *****************/


    /**
     * Returns the current term
     */
    function get_current_term(){
        return PHPWS_Settings::get('hms','current_term');
    }

    /**
     * Sets the current term
     */
    function set_current_term($term){
        PHPWS_Settings::set('hms','current_term',$term);
    }

    /**
     * Returns an array where the keys are the 'term' column of the
     * hms_term table and the values are a text description of the term
     * 
     * Useful for generating a drop down box of terms.
     */
    function get_available_terms_list()
    {
        
        $db = &new PHPWS_DB('hms_term');
        $db->addOrder('DESC');
        $results = $db->select();

        if(PEAR::isError($results)){
            PHPWS_Error::log($results);
            return $results;
        }

        # Creates a final result array using the 'term' column to generate a text description of the term as the value.
        $final = array();
        foreach ($results as $result){
            $final[$result['term']] = HMS_Term::term_to_text($result['term'], TRUE);
        }

        return $final;
    }

    /**
     * Returns the HTML for a DB pager of the current set of terms available
     */
    function get_available_terms_pager()
    {

        PHPWS_Core::initCoreClass('DBPager.php');

        $pager = &new DBPager('hms_term','HMS_Term');
        $pager->db->addOrder('term','DESC');

        $pager->setModule('hms');
        $pager->setTemplate('admin/term_pager.tpl');
        $pager->setLink('index.php?module=hms');
        $pager->setEmptyMessage("No terms found.");
        $pager->addToggle('class="toggle1"');
        $pager->addToggle('class="toggle2"');
        $pager->addRowTags('getRowTags');

        return $pager->get();
    }

    /**
     * Setups the the row tags for the db pager
     */
    function getRowTags()
    {
        $tags['TERM']   = HMS_Term::term_to_text($this->get_term(), TRUE);
        $tags['ACTION'] = "Make current  Delete"; // TODO

        return $tags;
    }

    /**
     * Creates a new term based on $_REQUEST data and saves it. Called by main() in response to a 'create_new_term' op.
     */
    function create_new_term(){
        
        if(!isset($_REQUEST['year_drop'])){
            return HMS_Term::show_edit_terms(NULL,'Error: Year not defined!');
        }

        if(!isset($_REQUEST['term_drop'])){
            return HMS_Term::show_edit_terms(NULL,'Error: Term not defined!');
        }

        $term = &new HMS_Term(NULL);
        $term->set_term(HMS_Term::text_to_term($_REQUEST['year_drop'],$_REQUEST['term_drop']));
        $result = $term->save();

        if(PEAR::isError($result)){
            return HMS_Term::show_edit_terms(NULL,'Error: There was a problem working with the database. The new term could not be created.');
        }else{
            return HMS_Term::show_edit_terms('Term created successfully!');
        }
    }

    /****************
     * UI Functions *
     ***************/

    function show_edit_terms($success = NULL, $error = NULL)
    {
        PHPWS_Core::initCoreClass('Form.php');
        PHPWS_Core::initModClass('hms','HMS_Util.php');
        
        $form = &new PHPWS_Form('new_term_form');

        $form->addHidden('module','hms');
        $form->addHidden('type','term');
        $form->addHidden('op','create_new_term');
        
        $form->addDropBox('year_drop',HMS_Util::get_years_2yr());
        $form->setLabel('year_drop','Year: ');
        
        $form->addDropBox('term_drop',HMS_Term::get_term_list());
        $form->setLabel('term_drop','Semester: ');
        
        $form->addSubmit('submit','Add Term');

        $tpl['TITLE'] = 'Edit Terms';
        $tpl['PAGER'] = HMS_Term::get_available_terms_pager();

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }else if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }

        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/edit_terms.tpl');
    }

    /**************************
     * Static Utility Methods *
     *************************/
     
    /**
    * Returns an array of the list of terms. Useful for constructing 
    * drop down menus. Array is keyed using the TERM_* defines.
    */
    function get_term_list(){
        $terms = array();

        $terms[TERM_SPRING]  = SPRING;
        $terms[TERM_SUMMER1] = SUMMER1;
        $terms[TERM_SUMMER2] = SUMMER2;
        $terms[TERM_FALL]    = FALL;

        return $terms;
    }
    
    /**
     * Takes a term in the form 'yyyytt' and
     * returns an array with keys 'year' and 'term' 
     * holding a string description of the term given.
     * 
     * If $concat parameter is TRUE, the function concatinates the 
     * array values and returns a single string (i.e. "spring 2008").
     */
    function term_to_text($term, $concat = FALSE)
    {
        $result = array();
        
        # Grab the year from the entry_term
        $result['year'] = substr($term, 0, 4);

        # Grab the term from the entry_term
        $sem = substr($term, 4, 2);
        
        if($sem == TERM_SPRING){
            $result['term'] = SPRING;
        }else if($sem == TERM_SUMMER1){
            $result['term'] = SUMMER1;
        }else if($sem == TERM_SUMMER2){
            $result['term'] = SUMMER2;
        }else if($sem == TERM_FALL){
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
     * Takes a year string and a TERM_* define and
     * returns an integer in the 'yyyytt' format used in banner.
     */
    function text_to_term($year, $term)
    {
        return $year . $term;
    }

    /******************************
     * Accessor & Mutator Methods *
     *****************************/

    function get_term(){
        return $this->term;
    }

    function set_term($term){
        $this->term = $term;
    }
}

?>
