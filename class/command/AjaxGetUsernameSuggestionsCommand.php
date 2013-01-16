<?php

PHPWS_Core::initModClass('hms', 'Command.php');

class AjaxGetUsernameSuggestionsCommand extends Command {

    private $username;

    public function getRequestVars(){
        return array('action'=>'AjaxGetUsernameSuggestions');
    }

    public function execute(CommandContext $context)
    {
        PHPWS_Core::initModClass('hms', 'Term.php');

        $searchString = $context->get('banner_id');
        
        // If the search string is empty, just return an empty json array
        if(!isset($searchString) || $searchString == ''){
            $context->setContent(json_encode(array()));
            return;
        }

        // String any non-alphanumeric characters
        
        // TODO: Check for a direct banner ID match
        
        // TODO: Check for a direct user name match
        
        // Fuzzy Search settings
        $tokenLimit = 2;  // Max number of tokens to parse, anything else is ignored
        $fuzzyTolerance = 3; // Levenshtein distance allowed between the metaphones of a token and a $fuzzyField
        
        
        $db = new PHPWS_DB('hms_student_autocomplete');

        // Initialize arrays for constructing db query
        $columnList  = array();
        $orderByList = array();
        $whereGroups = array(); 
        
        // Tokenize the passed in string
        $tokenCount = 0;
        $tokens = array();
        $token = strtok($searchString, "\n\t, "); // tokenize on newline, tab, comma, space

        while($token !== false && $tokenCount < $tokenLimit){
            $tokenCount++;
            $tokens[] = trim(strtolower($token)); // NB: must be lowercase!
            // tokenize on newline, tab, comma, space
            // NB: Don't pass in the string to strtok after the first call above
            $token = strtok("\n\t, ");
        }
        
        for($i = 0; $i < $tokenCount; $i++){
            // Add column for least value of (lev-distance between token and first name, lev-distance between token and last name)
            $columnList[] = "LEAST(levenshtein('{$tokens[$i]}', lower(last_name)), levenshtein('{$tokens[$i]}', lower(first_name)), levenshtein('{$tokens[$i]}', lower(middle_name))) as t{$i}_lev";
            // Add column for least value of (lev-distance between token and metaphone of first name, lev distance between token and metaphone of last name)
            $columnList[] = "LEAST(levenshtein(metaphone('{$tokens[$i]}', 10), last_name_meta), levenshtein(metaphone('{$tokens[$i]}', 10), first_name_meta), levenshtein(metaphone('{$tokens[$i]}', 10), middle_name_meta)) as t{$i}_metalev";
            
            // Add WHERE clauses for those fields
            $whereGroups['lev_where'][] = "fuzzy.t{$i}_lev < 3";
            $whereGroups['metaphone_where'][] = "fuzzy.t{$i}_metalev < {$fuzzyTolerance}";

            // Add to ORDER BY list
            $orderByList[] = "fuzzy.t{$i}_lev";
            $orderByList[] = "fuzzy.t{$i}_metalev";
        }
        
        $subQuery = "SELECT *, " . implode(", ", $columnList) . " FROM hms_student_autocomplete";
        
        // TODO limit by term (only current and future terms)
        // TODO limit by assigned in current/future terms
        
        $sql = "SELECT banner_id, username, first_name, last_name, middle_name FROM ($subQuery) as fuzzy WHERE (" . implode(' OR ', $whereGroups['lev_where']) . ") AND (" . implode(' OR ', $whereGroups['metaphone_where']) . ") ORDER BY " . implode(', ', $orderByList) . " LIMIT 10";
        
        
        $result = PHPWS_DB::getAll($sql);
        
        //test($result,1);
        
        $jsonResult = array('results'=>$result);
        
        $context->setContent(json_encode($jsonResult));
    }
}

?>