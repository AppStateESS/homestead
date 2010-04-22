<?php

class Student {

	private $username;
	private $bannerId;

	private $firstName;
	private $middleName;
	private $lastName;

	private $gender;
	private $dob;

	private $applicationTerm;
	private $type;
	private $class;
	private $creditHours;
	
	private $depositDate;
	private $depositWaived;

	private $addressList;
	private $phoneNumberList;

	public function __construct()
	{

	}

	public function getName()
	{
		return $this->firstName . ' ' . $this->lastName;
	}

	public function getFullName(){
		return $this->firstName . ' ' . $this->middleName . ' ' . $this->lastName;
	}

	public function getPrintableGender()
	{
		switch($this->gender){
			case FEMALE:
				return FEMALE_DESC;
				break;
			case MALE:
				return MALE_DESC;
				break;
			default:
				return 'Invalid gender';
		}
	}

	public function getPrintableType()
	{
		switch($this->type){
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
			default:
				return 'Unknown type: ' . $this->type;
				break;
		}
	}

	public function getPrintableClass()
	{
		switch($this->class){
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
				return 'Unknown class: ' . $this->class;
		}
	}

	public function getProfileLink()
	{
		$profileCmd = CommandFactory::getCommand('ShowStudentProfile');
		$profileCmd->setUsername($this->getUsername());
		return $profileCmd->getLink($this->getName());
	}

	public function getFullNameProfileLink()
	{
		$profileCmd = CommandFactory::getCommand('ShowStudentProfile');
		$profileCmd->setUsername($this->getUsername());
		return $profileCmd->getLink($this->getFullName());
	}

    public function getEmailLink()
    {
        return '<a href="mailto:'.$this->getUsername().'@appstate.edu">'.$this->getUsername().'@appstate.edu</a>';
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
	public function getAddress($type = ADDRESS_PRMT_RESIDENCE)
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
	 * Uses data returned from get_data.
	 */
	public function getAddressLine()
	{
		$addr = $this->getAaddress();

		if(!$addr){
			return false;
		}

		$line2 = ($addr->line2 != NULL && $addr->line2 != '') ? ($addr->line2 . ', ') : '';
		$line3 = ($addr->line3 != NULL && $addr->line3 != '') ? ($addr->line3 . ', ') : '';

		return "{$addr->line1}, $line2$line3{$addr->city}, {$addr->state} {$addr->zip}";
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
		return $this->bannerId;
	}

	public function setBannerId($id){
		$this->bannerId = $id;
	}

	public function getFirstName(){
		return $this->firstName;
	}

	public function setFirstName($name){
		$this->firstName = $name;
	}

	public function getMiddleName(){
		return $this->middleName;
	}

	public function setMiddleName($name){
		$this->middleName = $name;
	}

	public function getLastName(){
		return $this->lastName;
	}

	public function setLastName($name){
		$this->lastName = $name;
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

	public function getDOB(){
		return $this->dob;
	}

	public function setDOB($dob){
		$this->dob = $dob;
	}

	public function getApplicationTerm(){
		return $this->applicationTerm;
	}

	public function setApplicationTerm($term){
		$this->applicationTerm = $term;
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
		return $this->creditHours;
	}
	
	public function setCreditHours($hrs){
		$this->creditHours = $hrs;
	}

	public function getDepositDate(){
	    return $this->depositDate;
	}
	
	public function setDepositDate($date){
	    $this->depositDate = $date;
	}
	
	public function depositWaived(){
	    return $this->depositWaived;
	}
	
	public function setDepositWaived($status){
	    $this->depositWaived = $status;
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
}