<?php

PHPWS_Core::initModClass('hms', 'Command.php');
PHPWS_Core::initModClass('hms', 'Term.php');

class AjaxGetUsernameSuggestionsCommand extends Command {


    private $db;
    private $searchString;
    private $tokens;
    private $hmsTerm;
    
    const tokenLimit        = 2;
    const fuzzyTolerance    = 3;
    const resultLimit       = 10;
    
    public function getRequestVars(){
        return array('action'=>'AjaxGetUsernameSuggestions');
    }

    public function execute(CommandContext $context)
    {

        $this->searchString = $context->get('term'); // NB: this is the *search term*, not the semester
        
        $this->hmsTerm = Term::getCurrentTerm();
        
        // If the search string is empty, just return an empty json array
        if(!isset($this->searchString) || $this->searchString == ''){
            echo json_encode(array());
            exit;
        }

        // Strip any non-alphanumeric characters, escape slashes
        $this->searchString = pg_escape_string($this->searchString);
        
        // Check for a direct banner ID match
        if(preg_match("/[0-9]+/", $this->searchString)) {
            // String is all-numeric, probably a Banner ID
            // If the seach string is exactly 9 digits, then try to find a match
            if(preg_match("/[0-9]{9}/", $this->searchString)){
                $sql = $this->getBannerIdSearchSql();
            }else{
                // Otherwise, the banner id is incomplete, just echo the search string for a match
                // So just return an empty set
                $obj = new stdClass();
                $obj->banner_id = $this->searchString;
                $obj->name = '';
                $obj->username = '';
                echo json_encode(array($obj));
                exit;
            }
        }else{
            // Do fancy string matching instead
            $sql = $this->getFuzzyTextSql();
        }
        
        // TODO join for only assigned students / applied students in current/future terms
        // 
        
        // Add a limit on the number of results
        $sql .= " LIMIT " . self::resultLimit;
        
        //test($sql,1);
        
        $this->db = new PHPWS_DB('hms_student_autocomplete');
        
        $results = PHPWS_DB::getAll($sql);
        
        //test($results,1);
        
        if(is_null($results)){
            echo json_encode();
            exit;
        }
        
        // Log any DB errors and echo an empty result
        if(PHPWS_Error::logIfError($results)){
            echo json_encode(array());
            exit;
        }
        
        $resultObjects = array();
        foreach($results as $row){
            $obj = new stdClass();
            $obj->banner_id = $row['banner_id'];
            $obj->name      = $row['first_name'] . ' ' . $row['last_name'];
            $obj->username  = $row['username'];
            
            $resultObjects[] = $obj;
        }
        
        $jsonResult = json_encode($resultObjects);
        
        //test($jsonResult,1);
        
        echo $jsonResult;
        exit;
        
        // NB: using setContent adds escape characters to quotes in the JSON string... WRONG.
        //$context->setContent(json_encode($jsonResult));
    }
    
    private function getBannerIdSearchSql()
    {
        return "SELECT banner_id, username, first_name, last_name, middle_name FROM hms_student_autocomplete WHERE banner_id = {$this->searchString} AND (end_term >= {$this->hmsTerm} OR end_term IS NULL)";
    }
    
    private function getFuzzyTextSql()
    {
        // Initialize arrays for constructing db query
        $columnList  = array();
        $orderByList = array();
        $whereGroups = array();
        
        // Tokenize the passed in string
        $tokenCount = 0;
        $tokens = array();
        $token = strtok($this->searchString, "\n\t, "); // tokenize on newline, tab, comma, space
        
        while($token !== false && $tokenCount < self::tokenLimit){
            $tokenCount++;
            $tokens[] = trim(strtolower($token)); // NB: must be lowercase!
            // tokenize on newline, tab, comma, space
            // NB: Don't pass in the string to strtok after the first call above
            $token = strtok("\n\t, ");
        }
        
        for($i = 0; $i < $tokenCount; $i++){
            // Add column for least value of (lev-distance between token and first name, lev-distance between token and last name)
            $columnList[] = "LEAST(levenshtein('{$tokens[$i]}', last_name_lower), levenshtein('{$tokens[$i]}', first_name_lower), levenshtein('{$tokens[$i]}', middle_name_lower)) as t{$i}_lev";
            // Add column for least value of (lev-distance between token and metaphone of first name, lev distance between token and metaphone of last name)
            $columnList[] = "LEAST(levenshtein(metaphone('{$tokens[$i]}', 10), last_name_meta), levenshtein(metaphone('{$tokens[$i]}', 10), first_name_meta), levenshtein(metaphone('{$tokens[$i]}', 10), middle_name_meta)) as t{$i}_metalev";
        
            // Add WHERE clauses for those fields
            $whereGroups['lev_where'][] = "fuzzy.t{$i}_lev < 3";
            $whereGroups['metaphone_where'][] = "fuzzy.t{$i}_metalev < " . self::fuzzyTolerance;
        
            // Add to ORDER BY list
            $orderByList[] = "fuzzy.t{$i}_lev";
            $orderByList[] = "fuzzy.t{$i}_metalev";
        }
        
        $subQuery = "SELECT *, " . implode(", ", $columnList) . " FROM hms_student_autocomplete";
        
        // TODO limit by term (only current and future terms)
        // TODO limit by assigned in current/future terms
        
        $sql = "SELECT banner_id, username, first_name, last_name, middle_name FROM ($subQuery) as fuzzy WHERE ((" . implode(' OR ', $whereGroups['lev_where']) . ") AND (" . implode(' OR ', $whereGroups['metaphone_where']) . ")) OR username ILIKE '%{$tokens[0]}%' ORDER BY " . implode(', ', $orderByList);
        
        return $sql;
    }
}

?>