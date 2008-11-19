<?php
/**
 * The HMS_Term class
 * Handles creating/deleting/modifying terms
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 */

class HMS_Term{

    var $term;
    var $banner_queue;
    var $new_applications;
    var $_new;

    public function HMS_Term($term = NULL)
    {
        $this->_new = FALSE;
        if(!isset($term)){
            $this->_new = TRUE;
            return;
        }

        $this->set_term($term);

        $result = $this->init();
        if($result === FALSE) {
            return FALSE;
        }
    }

    public function init()
    {
        if(!isset($this->term)){
            return FALSE;
        }

        $db = &new PHPWS_DB('hms_term');
        $db->addWhere('term',$this->get_term());
        $result = $db->select('row');

        if(PHPWS_Error::logIfError($result)) {
            $this->term = NULL;
            return FALSE;
        }

        $this->banner_queue     = $result['banner_queue'];
        $this->new_applications = $result['new_applications'];

        return $result;
    }

    public function save()
    {
        $db = &new PHPWS_DB('hms_term');
        if(!$this->_new) {
            $db->addWhere('term', $this->get_term());
        }
        $result = $db->saveObject($this, FALSE, FALSE);

        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
        }

        return $result;
    }
    
    /**
     * Boolean test for the current term
     */
    public function is_current_term(){
        return $this->get_term() == HMS_Term::get_current_term();
    }
    
    /**
     * Boolean test for if this term is currently being used by the user.
     */
    public function is_selected_term()
    {
        return $this->get_term() == HMS_Term::get_selected_term();
    }
    
    /*************
     * Main menu *
     ************/
    
    public function main()
    {
        if( !Current_User::allow('hms', 'edit_terms') && !Current_User::allow('hms', 'select_term') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        switch($_REQUEST['op'])
        {
            case 'show_create_term':
                return HMS_Term::show_create_term();
                break;
            case 'show_edit_terms':
                return HMS_Term::show_edit_terms();
                break;
            case 'create_new_term':
                return HMS_Term::create_new_term();
                break;
            case 'term_select':
                return HMS_Term::term_select();
                break;
            case 'term_activate':
                return HMS_Term::term_activate();
                break;
            case 'term_delete':
                return HMS_Term::term_delete();
                break;
            case 'show_term_association':
                return HMS_Term::show_term_association();
                break;
            case 'edit_term_association':
                return HMS_Term::edit_term_association();
                break;
            case 'delete_term_association':
                return HMS_Term::delete_term_association();
                break;
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
    public function get_current_term(){
        return PHPWS_Settings::get('hms','current_term');
    }

    /**
     * Sets the current term
     */
    public function set_current_term($term){
        PHPWS_Settings::set('hms','current_term',$term);
        PHPWS_Settings::save('hms');

        PHPWS_Core::initModClass('hms', 'HMS_Activity_Log.php');
        $username = Current_User::getUsername();
        HMS_Activity_Log::log_activity($username, ACTIVITY_CHANGE_ACTIVE_TERM, $username, "Active term set by $username to $term");
    }


    /**
     * Returns the current term the user has selected. If no term has been selected
     * yet then the 'current' term is returned.
     */
    public function get_selected_term()
    {
        if(isset($_SESSION['selected_term'])) {
            return $_SESSION['selected_term'];
        } else {
            return HMS_Term::get_current_term();
        }
    }

    /**
     * Sets the 'activate' term by saving the given term in the session variable.
     */
    public function set_selected_term($term)
    {
        $_SESSION['selected_term'] = $term;
        return;
    } 

    /**
     */
    public function is_banner_queue_enabled($term)
    {
        $term = &new HMS_Term($term);
        return $term->get_banner_queue();
    }

    /**
     * Returns an array where the keys are the 'term' column of the
     * hms_term table and the values are a text description of the term
     * 
     * Useful for generating a drop down box of terms.
     */
    public function get_available_terms_list()
    {
        
        $db = &new PHPWS_DB('hms_term');
        $db->addOrder('term DESC');
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
    public function get_available_terms_pager()
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
    public function getRowTags()
    {
        $tags['TERM'] = HMS_Term::term_to_text($this->get_term(), TRUE);
        $actions = array();
        
        // 'Select' selects the term the user works in
        if($this->is_selected_term()) {
            $actions[] = '<strong>Selected</strong>';
        } else {
            if(Current_User::allow('hms','select_term')){
                $actions[] = PHPWS_Text::secureLink(_('Select'), 'hms',
                    array('type'=>'term',
                          'op'  =>'term_select',
                          'term'=> $this->get_term()));
            }
        }
        
        // 'Activate' makes the term globally active
        if($this->is_current_term()){
            $actions[] = '<strong>Active</strong>';
        }else{
            if(Current_User::allow('hms', 'activate_term')) {
                $confirm = array();
                $confirm['QUESTION'] =
                    'This will set the HMS Active Term to '.$tags['TERM'].
                    '.  This setting applies to all users of HMS and will '.
                    'affect the Housing Application process.  Click OK to '.
                    'set the Active Term to '.$tags['TERM'].'.';
                $confirm['ADDRESS'] =
                    PHPWS_Text::linkAddress('hms',
                        array('type'=>'term',
                              'op'  =>'term_activate',
                              'term'=>$this->get_term()),
                        TRUE);
                $confirm['LINK'] = _('Activate');
                $actions[] = Layout::getJavascript('confirm', $confirm);
            } 
        }

        // 'Delete' does exactly that
        if(Current_User::allow('hms', 'edit_terms')) {
            $actions[] = PHPWS_Text::secureLink(_('Delete'), 'hms',
                array('type'=>'term',
                      'op'  =>'term_delete',
                      'term'=> $this->get_term()));
        }

        // 'Banner Queue' toggles whether it's enabled or not
        if(Current_User::allow('hms', 'banner_queue')) {
            $confirm = array();
            if($this->get_banner_queue() == 0) {
                $confirm['QUESTION'] =
                    'This will enable the Banner Queue for '. $tags['TERM'].
                    '.  Any changes made in HMS will be queued up and NOT '.
                    'sent to Banner. Click OK to enable the Banner Queue.';
                $confirm['ADDRESS'] =
                    PHPWS_Text::linkAddress('hms',
                        array('type'=>'banner_queue',
                              'op'  =>'enable',
                              'term'=>$this->get_term()),
                        TRUE);
                $confirm['LINK'] = 'Disabled';
            } else {
                $confirm['QUESTION'] =
                    'This will flush and then disable the Banner Queue for '.
                    $tags['TERM'].'.  THIS WILL TAKE SEVERAL MINUTES.  Any '.
                    'changes made in HMS will be sent immediately to Banner. '.
                    'Click OK to disable the Banner Queue.';
                $confirm['ADDRESS'] =
                    PHPWS_Text::linkAddress('hms',
                        array('type'=>'banner_queue',
                              'op'  =>'disable',
                              'term'=>$this->get_term()),
                        TRUE);
                $confirm['LINK'] = 'Enabled';
            }
            $tags['BANNER_QUEUE'] = Layout::getJavascript('confirm', $confirm);
        } else {
            $tags['BANNER_QUEUE'] = 
                ($this->get_banner_queue() == 0 ? 'Disabled' : 'Enabled');
        }
        
        $tags['ACTION'] = implode(' | ', $actions);
        return $tags;
    }

    /**
     * Creates a new term based on $_REQUEST data and saves it. Called by main() in response to a 'create_new_term' op.
     */
     //TODO: Add public functionality here to call copy depending on what was selected in the 'copy_drop'
    public function create_new_term(){
        if( !Current_User::allow('hms', 'edit_terms') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        if(!isset($_REQUEST['year_drop'])){
            return HMS_Term::show_edit_terms(NULL,'Error: Year not defined!');
        }

        if(!isset($_REQUEST['term_drop'])){
            return HMS_Term::show_edit_terms(NULL,'Error: Term not defined!');
        }

        $db = new PHPWS_DB();
        
        //echo "beginning transaction<br>";
        $db->query('BEGIN');

        $term = &new HMS_Term(NULL);
        $term->set_term(HMS_Term::text_to_term($_REQUEST['year_drop'],$_REQUEST['term_drop']));
        $term->banner_queue     = 0;
        $term->new_applications = 0;
        //echo "saving new term<br>";
        $result = $term->save();

        if(PEAR::isError($result)){
            return HMS_Term::show_edit_terms(NULL,'Error: There was a problem working with the database. The new term could not be created.');
        }
        
        //Create an entry term association between this term and itself
        PHPWS_Core::initModClass('hms', 'HMS_Term_Applications.php');
        HMS_Term::set_valid_term($term->term, $term->term, 1); //set required term association with itself
        

        if($_REQUEST['copy_drop'] == 1){
            # Copy the hall structure & assignments
            $assignments = TRUE;
        }else{
            $assignments = FALSE;
        }

        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

        //echo "gettings halls<br>";
        # Get the halls from the current term
        $halls = HMS_Residence_Hall::get_halls();
        //test($halls);
        
        //echo "copying halls<br>";
        foreach ($halls as $hall){
            //echo "copying hall with id: $hall->id <br>";
            $result = $hall->copy($term->get_term(), $assignments);
            if(!$result){
                //echo "error returned from copying hall with id: $hall->id <br>";
                //test($result);
                //echo "rolling back<br>";
                $db->query('ROLLBACK');
                return HMS_Term::show_edit_terms(NULL, 'There was an error copying data. Please contact ESS.');
            }
        }

        //echo "done copying halls<br>";
        $db->query('COMMIT');

        return HMS_Term::show_edit_terms('Term created successfully!');
    }

    /**
      Called in response to the 'term_select' action. Saves the selected term in the session variable for use in other editing.
     */
    public function term_select()
    {
        if(!Current_User::allow('hms', 'select_term')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }
        
        HMS_Term::set_selected_term($_REQUEST['term']);
        return HMS_Term::show_edit_terms('Term ' . HMS_Term::term_to_text($_REQUEST['term'], true) . ' selected.');
    }

    /**
     * Called in response to the 'term_activate' action. Saves the selected term in the PHPWS_Settings class.
     */
    public function term_activate()
    {
        if(!Current_User::allow('hms', 'activate_term')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        HMS_Term::set_current_term($_REQUEST['term']);
        return HMS_Term::show_edit_terms('Term ' . HMS_Term::term_to_text($_REQUEST['term'], true) . ' activated.');
    }

    /**
     * Called in response to the 'term_delete action. Not yet impletemented.
     */
    public function term_delete()
    {
        if( !Current_User::allow('hms', 'edit_terms') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        /*
         * When term deletion is implemented make sure to delete all references
         * to that term in the valid application dates table.
         */

        return HMS_Term::show_edit_terms(NULL, 'Sorry, term deletion is not yet implemented.');
    }

    /****************
     * UI Functions *
     ***************/

    public function show_create_term($success = NULL, $error = NULL)
    {
        if( !Current_User::allow('hms', 'edit_terms') ){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        PHPWS_Core::initCoreClass('Form.php');
        PHPWS_Core::initModClass('hms','HMS_Util.php');

        $tpl['TITLE'] = 'Add a New Term';

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }else if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }
        
        $form = &new PHPWS_Form('new_term_form');

        $form->addHidden('module','hms');
        $form->addHidden('type','term');
        $form->addHidden('op','create_new_term');
        
        $form->addDropBox('year_drop',HMS_Util::get_years_2yr());
        $form->setLabel('year_drop','Year: ');
        
        $form->addDropBox('term_drop',HMS_Term::get_term_list());
        $form->setLabel('term_drop','Semester: ');

        $form->addDropBox('copy_drop', array(0 => 'Hall structure only', 1 => 'Hall structure & assignments'));
        $form->setLabel('copy_drop', 'What to copy: ');
        
        $form->addSubmit('submit','Add Term');
        
        $form->mergeTemplate($tpl);
        $tpl = $form->getTemplate();

        return PHPWS_Template::process($tpl, 'hms', 'admin/add_term.tpl');
    }

    public function show_edit_terms($success = NULL, $error = NULL)
    {
        if( !Current_User::allow('hms', 'select_term') && !Current_User::allow('edit_terms') && !Current_User::allow('activate_term') && !Current_User::allow('banner_queue')){
            $tpl = array();
            return PHPWS_Template::process($tpl, 'hms', 'admin/permission_denied.tpl');
        }

        $tpl['TITLE'] = 'Edit Terms';

        if(Current_User::allow('hms', 'edit_terms'))
            $tpl['CREATE_TERM_LINK'] = PHPWS_Text::secureLink(_('Create a New Term'), 'hms', array('type'=>'term', 'op'=>'show_create_term'));
        

        $tpl['PAGER'] = HMS_Term::get_available_terms_pager();

        if(isset($success)){
            $tpl['SUCCESS_MSG'] = $success;
        }else if(isset($error)){
            $tpl['ERROR_MSG'] = $error;
        }       
        
        return PHPWS_Template::process($tpl, 'hms', 'admin/edit_terms.tpl');
    }

    public function delete_term_association()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term_Applications.php');
        HMS_Term_Applications::remove($_REQUEST['delete']);
        
        return HMS_Term::show_term_association();
    }

    public function edit_term_association()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term_Applications.php');
        $error   = null;
        $success = null;
        if(isset($_REQUEST['term1']) && isset($_REQUEST['term2']) 
           && is_numeric($_REQUEST['term1']) && is_numeric($_REQUEST['term2']))
        {
            if(!HMS_Term::set_valid_term($_REQUEST['term1'], $_REQUEST['term2'],
               (isset($_REQUEST['required']) ? 1 : 0)))
            {
                $error   .= "<font color=red>Error associating terms.</font>";
            } else {
                $success .= "<font color=green>Terms Associated.</font>";
            }
        }
        
        return HMS_Term::show_term_association($success, $error);
    }

    public function show_term_association($success = NULL, $error = NULL)
    {
        PHPWS_Core::initModClass('hms', 'HMS_Term_Applications.php');
        $tpl = array();
        if( isset($success) && !is_null($success)){
            $tpl['MESSAGE'] = $success;
        } elseif(!is_null($error)){
            $tpl['ERROR'] = $error;
        }
        
        $terms = HMS_Term::get_available_terms_list();
        $form = &new PHPWS_Form('associate_terms');
        $form->addSelect('term1', $terms);
        $form->addSelect('term2', $terms);
        $form->addCheck('required', 'yes');
        $form->addSubmit('submit', 'Make Association');

        $form->addHidden('type',    'term');
        $form->addHidden('op',      'edit_term_association');

        $form->mergeTemplate($tpl);
        
        $pager = HMS_Term_Applications::getPager();
        $pager->addPageTags($form->getTemplate());

        return $pager->get();
    }

    /**************************
     * Static Utility Methods *
     *************************/
     
    /**
    * Returns an array of the list of terms. Useful for constructing 
    * drop down menus. Array is keyed using the TERM_* defines.
    */
    public function get_term_list()
    {
        $terms = array();

        $terms[TERM_SPRING]  = SPRING;
        $terms[TERM_SUMMER1] = SUMMER1;
        $terms[TERM_SUMMER2] = SUMMER2;
        $terms[TERM_FALL]    = FALL;

        return $terms;
    }

    /**
     * Returns TRUE if the given term has been create, FALSE otherwise
     */
    public function check_term_exists($term)
    {
        $db = &new PHPWS_DB('hms_term');
        $db->addWhere('term', $term, '=');
        $result = $db->count();

        if(PEAR::isError($result)){
            PHPWS_Error::log($result);
            return FALSE;
        }

        if($result == 1){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    /**
     * Takes a term in the form 'yyyytt' and
     * returns an array with keys 'year' and 'term' 
     * holding a string description of the term given.
     * 
     * If $concat parameter is TRUE, the public function concatinates the 
     * array values and returns a single string (i.e. "spring 2008").
     */
    public function term_to_text($term, $concat = FALSE)
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
    public function text_to_term($year, $term)
    {
        return $year . $term;
    }

    /**
     * Returns the 'next term' based on the term passed in
     */
    public function get_next_term($term)
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
      * Returns a list application terms that can be applied for at the same
      * time as the parameter $term, and whether or not that term is required.
      *
      * @param integer Term to check
      * @return array $arr[$index] = array(0 => $valid_term, 1 => $required);
      */
    public function get_valid_application_terms($term){
        $db = &new PHPWS_DB('hms_term_applications');
        $db->addWhere('app_term', $term);
        $db->addOrder('term asc');
        $result = $db->select();

        if(PHPWS_Error::logIfError($result)){
            return false;
        }

        return $result;
    }

    /**
      * Create a valid term association.
      *
      * @param integer $key
      * @param integer $value
      * @param bool    $required
      *
      * @return bool   $success
      */
    public function set_valid_term($key, $value, $required){
        $db = &new PHPWS_DB('hms_term_applications');
        $db->addValue('app_term', $key);
        $db->addValue('term', $value);
        $db->addValue('required', $required);
        $result = $db->insert();

        if(PHPWS_Error::logIfError($result)){
            return false;
        } 
        
        return true;
    }

    /*
     * Check a term association.
     *
     * @param integer $key
     * @param integer $value
     *
     * @return bool   $success
     */
    public function is_valid_term($key){
        $db = &new PHPWS_DB('hms_term_applications');
        $db->addwhere('app_term', $key);
        $db->addWhere('term', $key, '=', 'OR');
        $result = $db->select();

        if(PHPWS_Error::logIfError($result) || sizeof($result) == 0){
            return false;
        }

        return true;
    }

    public function get_term_year($term)
    {
        # Grab the year
        return substr($term, 0, 4);
    }

    public function get_term_sem($term)
    {
        return substr($term, 4, 2);
    }

    /******************************
     * Accessor & Mutator Methods *
     *****************************/

    public function get_term(){
        return $this->term;
    }

    public function set_term($term){
        $this->term = $term;
    }

    public function get_banner_queue() {
        return $this->banner_queue;
    }

    public function enable_banner_queue() {
        $this->banner_queue = 1;
    }

    public function disable_banner_queue() {
        $this->banner_queue = 0;
    }
}

?>
