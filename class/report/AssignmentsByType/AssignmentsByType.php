<?php

/**
 * The Assignments By Type Report.
 * 
 * Gives a breakdown of assignments by their assignment reason
 * for the given term.
 * 
 * @author jbooker
 * @package HMS
 */

class AssignmentsByType extends Report {
    
    const friendlyName = 'Assignments By Type';
    const shortName    = 'AssignmentsByType';
    
    private $term;
    
    private $typeCounts;
    
    public function __construct($id = 0)
    {
        parent::__construct($id);
        
        $this->typeCounts = array();
    }
    
    public function execute()
    {
        $this->typeCounts = PHPWS_DB::getAssoc("select reason, count(*) from hms_assignment where term = {$this->term} group by reason order by reason");
        
        if(PHPWS_Error::isError($this->typeCounts)){
            throw new DatabaseException($this->typeCounts->toString());
        }
    }
    
    /****************************
     * Accessor/Mutator Methods *
    ****************************/
    
    public function setTerm($term){
        $this->term = $term;
    }
    
    public function getTerm(){
        return $this->term;
    }
    
    public function getTypeCounts()
    {
        return $this->typeCounts;
    }
}

