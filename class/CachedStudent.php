<?php 

PHPWS_Core::initModClass('hms', 'Student.php');

class CachedStudent extends Student {

    private $term;
    private $expires_on; // Unix timestamp of when this record expires
    
    public function __construct(){
        parent::__construct();
        
    }
    
    public function load()
    {
        //TODO
    }
    
    public function save() 
    {
        //TODO
    }
    
    public function isCached()
    {
        //TODO
    }
}

?>