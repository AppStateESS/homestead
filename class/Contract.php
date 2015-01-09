<?php
class Contract {
	
    protected $id;
    protected $banner_id;
    protected $term;
    protected $envelope_id;
    
    // TODO: make first parameter an instance of $student
    public function __construct($student, $term, $envelopeId)
    {
    	$this->banner_id = $student->getBannerId();
        $this->term = $term;
        $this->envelope_id = $envelopeId;
    }
    
    public function getId()
    {
    	return $this->id;
    }
    
    public function setId($id)
    {
    	$this->id = $id;
    }
    
    
    public function getBannerId()
    {
    	return $this->banner_id;
    }
    
    public function getTerm()
    {
    	return $this->term;
    }
    
    public function getEnvelopeId()
    {
    	return $this->envelope_id;
    }
}

class ContractRestored extends Contract {
	public function __construct(){} // Empty constructor for loading from DB 
}
?>