<?php

class Student {

    // TODO make these all private and make sure nothing breaks
    public $username;
    public $banner_id;

    public $first_name;
    public $middle_name;
    public $last_name;
    public $preferred_name;

    public $dob;
    public $gender;

    public $confidential; // This student is super secret

    public $application_term;
    public $type;
    public $class;
    public $credit_hours;

    public $student_level;
    public $international;

    public $admissions_decision_code;
    public $admissions_decision_desc;

    public $honors;
    public $teaching_fellow;
    public $watauga_member;
    public $greek;

    public $disabled_pin;
    public $housing_waiver; // Whether or not a freshmen's student on-campus housing has been waived (e.g., living close by with family)

    public $addressList;
    public $phoneNumberList;


    // Data Source - Not saved to the DB.
    // Used to determine the class name that provided the data.
    private $dataSource;

    public function __construct()
    {

    }

    public function getName()
    {
        if(isset($this->preferred_name) && $this->preferred_name != '') {
        	return $this->getPreferredName() . ' ' . $this->getLastName();
        }
        
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    public function getFullName(){
        
        if(isset($this->preferred_name) && $this->preferred_name != '') {
            $firstName = $this->getPreferredName();
        } else {
        	$firstName = $this->getFirstName();
        }
        
        return $firstName . ' ' . $this->getMiddleName() . ' ' . $this->getLastName();
    }
    
    public function getLegalName()
    {
    	return $this->getFirstName() . ' ' . $this->getMiddleName() . ' ' . $this->getLastName();
    }

    public function getFullNameInverted()
    {
        if(isset($this->preferred_name) && $this->preferred_name != '') {
            $firstName = $this->getPreferredName();
        } else {
            $firstName = $this->getFirstName();
        }
        
        return $this->getLastName() . ', ' . $firstName . ' ' . $this->getMiddleName();
    }
    
    public function getPrintableGender()
    {
        $gender = $this->getGender();

        if ($gender === '' || is_null($gender)) {
            throw new InvalidArgumentException('Missing gender.');
        }

        if ($gender == FEMALE) {
            return FEMALE_DESC;
        } else if ($gender == MALE) {
            return MALE_DESC;
        }

        // If we make it here, there's a big problem.
        throw new InvalidArgumentException('Invalid gender.');
    }

    public function getPrintableGenderAbbreviation()
    {
        $gender = $this->getGender();

        if ($gender === '' || is_null($gender)) {
            throw new InvalidArgumentException('Missing gender.');
        }

        if ($gender == FEMALE) {
            return 'F';
        } else if ($gender == MALE) {
            return 'M';
        }

        // If we make it here, there's a big problem.
        throw new InvalidArgumentException('Invalid gender.');
    }

    public function getPrintableType()
    {
        switch($this->getType()){
            case TYPE_FRESHMEN:
                return 'Freshmen';
                break;
            case TYPE_TRANSFER:
                return 'Transfer';
                break;
            case TYPE_CONTINUING:
                return 'Continuing';
                break;
            case TYPE_RETURNING:
                return 'Returning';
                break;
            case TYPE_READMIT:
                return 'Re-admit';
                break;
            case TYPE_WITHDRAWN:
                return 'Withdrawn';
                break;
            case TYPE_NONDEGREE:
                return 'New non-degree';
                break;
            case TYPE_GRADUATE:
                return 'Graduate';
                break;
            default:
                return 'Unknown type: ' . $this->type;
                break;
        }
    }

    public function getPrintableClass()
    {
        switch($this->getClass()){
            case CLASS_FRESHMEN:
                return 'Freshmen';
                break;
            case CLASS_SOPHOMORE:
                return 'Sophomore';
                break;
            case CLASS_JUNIOR:
                return 'Junior';
                break;
            case CLASS_SENIOR:
                return 'Senior';
                break;
            default:
                return 'Unknown class: ' . $this->getClass();
        }
    }

    public function getPrintableLevel()
    {
        switch($this->getStudentLevel()){
            case LEVEL_UNDERGRAD:
                return 'Undergraduate';
                break;
            case LEVEL_GRAD:
                return 'Graduate';
                break;
            case LEVEL_GRAD2:
                return 'Graduate II';
                break;
            case LEVEL_DOCTORAL:
                return 'Doctoral';
                break;
            case LEVEL_SPECIALIST:
                return 'Specialist';
                break;
            case LEVEL_UNDECLARED:
                return 'Undeclared';
                break;
            default:
                return 'Unknown level ' . $this->getStudentLevel();
        }
    }

    public function getProfileLink()
    {
        $profileCmd = CommandFactory::getCommand('ShowStudentProfile');
        $profileCmd->setUsername($this->getUsername());
        return $profileCmd->getLink($this->getName());
    }

    /**
     * @deprecated 0.4.87 - Nov 3, 2014
     * @see getProfileLink()
     */
    public function getFullNameProfileLink()
    {
        return $this->getProfileLink();
    }

    public function getEmailLink()
    {
        return '<a href="mailto:'.$this->getUsername().'@appstate.edu">'.$this->getUsername().'@appstate.edu</a>';
    }
    
    public function getEmailAddress()
    {
        //TODO make the domain configurable
    	return $this->getUsername() . '@appstate.edu';
    }

    /**
     * Returns an associate array with keys:
     * line1, line2, line3, city, county, state, zip
     * 'county' is a county code
     * 'state' is a two character abbrev.
     *
     * Passing a type of 'null' will cause a 'PR' address to
     * be returned, or a 'PS' addresses if no PR exists.
     *
     * Valid options for 'type' are the address types defined in inc/defines.php:
     * null (default, returns 'PR' if exists, otherwise 'PS')
     * ADDRESS_PRMT_RESIDENCE ('PR' - permanent residence)
     * ADDRESS_PRMT_STUDENT   ('PS' - permanent student)
     */
    public function getAddress($type = null)
    {
        $pr_address = null;
        $ps_address = null;

        foreach($this->addressList as $address){
            if(((string)$address->atyp_code) == ADDRESS_PRMT_RESIDENCE) {
                $pr_address = $address;
            }else if(((string)$address->atyp_code) == ADDRESS_PRMT_STUDENT){
                $ps_address = $address;
            }
        }

        # Decide which address type to return, based on $type parameter
        if(is_null($type)){
            # Return the pr address, if it exists
            if(!is_null($pr_address)){
                return $pr_address;
                # Since there was no ps address, return the ps address, if it exists
            }else if(!is_null($ps_address)){
                return $ps_address;
            }else{
                # No address found, return false
                return false;
            }
        }else if($type == ADDRESS_PRMT_RESIDENCE && !is_null($pr_address)){
            return $pr_address;
        }else if($type == ADDRESS_PRMT_STUDENT && !is_null($ps_address)){
            return $ps_address;
        }else{
            # Either a bad type was specified (i.e. not null and not PS or PR)
            # or the specified type was not found
            return false;
        }

        # Since we got here without finding the requested address, just return false
        return false;
    }

    /**
     * Returns an address formatted as one line, like so:
     * "line1, (line 2, )(line 3, )city, state, zip"
     * @param $addrType String Two letter address type code, same as for getAddress()
     */
    public function getAddressLine()
    {
        $addr = $this->getAddress();

        if(!$addr){
            return false;
        }

        $line2 = ($addr->line2 != NULL && $addr->line2 != '') ? ($addr->line2 . ', ') : '';
        $line3 = ($addr->line3 != NULL && $addr->line3 != '') ? ($addr->line3 . ', ') : '';

        return "{$addr->line1}, $line2$line3{$addr->city}, {$addr->state} {$addr->zip}";
    }

    public function getComputedClass($baseTerm)
    {

        // Break up the term and year
        $yr     = floor($this->application_term / 100);
        $sem    = $this->application_term - ($yr * 100);

        $curr_year = floor($baseTerm / 100);
        $curr_sem  = $baseTerm - ($curr_year * 100);

        if($curr_sem == 10) {
            $curr_year -= 1;
            $curr_sem   = 40;
        }

        if(is_null($this->application_term) || !isset($this->application_term)) {
            throw new InvalidArgumentException('Missing application term!');
        }else if($this->application_term >= $baseTerm) {
            // The application term is greater than the current term, then they're certainly a freshmen
            return CLASS_FRESHMEN;
        }else if(
                ($yr == $curr_year + 1 && $sem = 10) ||
                ($yr == $curr_year && $sem >= 20 && $sem <= 40)) {
            // freshmen
            return CLASS_FRESHMEN;
        }else if(
                ($yr == $curr_year && $sem == 10) ||
                ($yr + 1 == $curr_year && $sem >= 20 && $sem <= 40)) {
            // soph
            return CLASS_SOPHOMORE;
        }else if(
                ($yr + 1 == $curr_year && $sem == 10) ||
                ($yr + 2 == $curr_year && $sem >= 20 && $sem <= 40)) {
            // jr
            return CLASS_JUNIOR;
        }else{
            // senior
            return CLASS_SENIOR;
        }
    }

    /***************************
     * Getter / Setter Methods *
    ***************************/

    public function getUsername(){
        return $this->username;
    }

    public function setUsername($username){
        $this->username = $username;
    }

    public function getBannerId(){
        return $this->banner_id;
    }

    public function setBannerId($id){
        $this->banner_id = $id;
    }

    public function getFirstName(){
        return $this->first_name;
    }

    public function setFirstName($name){
        $this->first_name = $name;
    }

    public function getMiddleName(){
        return $this->middle_name;
    }

    public function setMiddleName($name){
        $this->middle_name = $name;
    }

    public function getLastName(){
        return $this->last_name;
    }

    public function setLastName($name){
        $this->last_name = $name;
    }

    public function getPreferredName(){
        return $this->preferred_name;
    }

    public function setPreferredName($name){
        $this->preferred_name = $name;
    }

    public function getDOB(){
        return $this->dob;
    }

    public function setDOB($dob){
        $this->dob = $dob;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function setGender($gender){
        if($gender == 'M'){
            $this->gender = MALE;
            return;
        }

        if($gender == 'F'){
            $this->gender = FEMALE;
            return;
        }

        $this->gender = $gender;
        return;
    }

    public function getConfiential(){
        return $this->confidential;
    }

    public function setConfidential($conf){
        $this->confidential = $conf;
    }

    public function getApplicationTerm(){
        return $this->application_term;
    }

    public function setApplicationTerm($term){
        $this->application_term = $term;
    }

    public function getType(){
        return $this->type;
    }

    public function setType($type){
        $this->type = $type;
    }

    public function getClass(){
        return $this->class;
    }

    public function setClass($class){
        $this->class = $class;
    }

    public function getCreditHours(){
        return $this->credit_hours;
    }

    public function setStudentLevel($level){
        $this->student_level = $level;
    }

    public function getStudentLevel(){
        return $this->student_level;
    }

    public function setCreditHours($hrs){
        $this->credit_hours = $hrs;
    }

    public function setInternational($intl){
        $this->international = $intl;
    }

    public function isInternational(){
        return $this->international;
    }

    public function setHonors($hon){
        $this->honors = $hon;
    }

    public function isHonors(){
        return $this->honors;
    }

    public function setTeachingFellow($teach){
        $this->teaching_fellow = $teach;
    }

    public function isTeachingFellow(){
        return $this->teaching_fellow;
    }

    public function setWataugaMember($member){
        $this->watauga_member = $member;
    }

    public function isWataugaMember(){
        return $this->watauga_member;
    }

    public function setGreek($greek){
        $this->greek = $greek;
    }

    public function isGreek(){
        return $this->greek;
    }

    public function pinDisabled(){
        return $this->disabled_pin;
    }

    public function setPinDisabled($flag){
        $this->disabled_pin = $flag;
    }

    public function housingApplicationWaived(){
        return $this->housing_waiver;
    }

    public function setHousingWaiver($waiver){
        $this->housing_waiver = $waiver;
    }

    public function getAdmissionDecisionCode(){
        return $this->admissions_decision_code;
    }

    public function setAdmissionDecisionCode($code){
        $this->admissions_decision_code = $code;
    }

    public function getAdmissionDecisionDesc(){
        return $this->admissions_decision_desc;
    }

    public function setAdmissionDecisionDesc($desc){
        $this->admissions_decision_desc = $desc;
    }

    public function getAddressList(){
        return $this->addressList;
    }

    public function setAddressList(Array $list){
        $this->addressList = $list;
    }

    public function getPhoneNumberList(){
        return $this->phoneNumberList;
    }

    public function setPhoneNumberList(Array $list){
        $this->phoneNumberList = $list;
    }

    public function getDataSource(){
        return $this->dataSource;
    }

    public function setDataSource($source){
        $this->dataSource = $source;
    }
}
