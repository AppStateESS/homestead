<?php
namespace Homestead;

class Contract {
	
    private $bannerId;
    private $term;
    private $envelopeId;
    
    public function __construct($bannerId, $term, $envelopeId)
    {
    	$this->bannerId = $bannerId;
        $this->term = $term;
        $this->envelopeId = $envelopeId;
    }
}

class ContractRestored extends Contract {
	public function __construct(){} // Empty constructor for loading from DB 
}
?>