<?php

/**
 * Main report class for the Freshmen Applications over time report.
 *
 * @author Jeremy Booker
 * @package Hms
 */
class FreshmenApplicationsGraph extends Report {

    const friendlyName = 'Freshmen Applications Graph';
    const shortName = 'FreshmenApplicationsGraph';

    private $lastTerm;
    
    private $thisYearJson;
    private $lastYearJson;
    
    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getTerm()
    {
        return $this->term;
    }
    
    public function getLastTerm()
    {
        return $this->lastTerm;
    }
    
    public function execute()
    {
        $this->lastTerm = Term::getPreviousYear($this->term);
        
        $thisTermDate     = $this->getCumulativeCountsByTerm($this->term);
        
        $previousTermData = $this->getCumulativeCountsByTerm($this->lastTerm);
        
        // Subtract a year's worth of seconds from this year's records to make them line up on the graph with last year's
        $newArray = array();
        foreach($thisTermDate as $point) {
            $newArray[] = array('date'=>($point['date'] - 31536000), 'running_total'=>$point['running_total']);
        }
        
        $this->thisYearJson = $this->formatForFlot($newArray);
        $this->lastYearJson = $this->formatForFlot($previousTermData);
    }

    
    private function getCumulativeCountsByTerm($term)
    {
        // If the report is for fall, we really want Summer 1 and Summer 2 applications terms too.
        // So, build a list of extra application terms we should use.
        $extraTerms = array();
        if (Term::getTermSem($term) == TERM_FALL) {
            // Compute the Summer 2 term
            $t = Term::getPrevTerm($term);
            $extraTerms[] = $t;
        
            // Computer the SUmmer 1 term
            $t = Term::getPrevTerm($t);
            $extraTerms[] = $t;
        }
        
        // Create the where clause, start by adding the requested term
        $termClause = "application_term = {$term}";
        
        // Add any extra terms, if any.
        if (count($extraTerms) > 0) {
            foreach ($extraTerms as $t) {
                $termClause .= " OR application_term = $t";
            }
        }
        
        // Build the query
        /* Query with human readable dates
        $query = "select
        to_char(date_trunc('day',timestamp 'epoch' + created_on * interval '1 second'), 'Mon DD, YYYY') as date,
        count(created_on) as daily_count, sum(count(created_on)) OVER (ORDER BY to_char(date_trunc('day',timestamp 'epoch' + created_on * interval '1 second'), 'Mon DD, YYYY')) as running_total
        FROM hms_new_application
        WHERE term = 201340
        AND ($termClause)
        AND student_type = 'F'
        AND application_type = 'fall'
        GROUP BY date
        ORDER BY date";
        */
        
        PHPWS_Core::initCoreClass('PdoFactory.php');
        $db = PdoFactory::getInstance()->getPdo();
        
        $query ="SELECT
                date_part('epoch', date_trunc('day',timestamp 'epoch' + created_on * interval '1 second')) as date,
                SUM(COUNT(created_on)) OVER (ORDER BY date_part('epoch', date_trunc('day',timestamp 'epoch' + created_on * interval '1 second'))) as running_total
                FROM hms_new_application
                WHERE term = :term
                AND ($termClause)
                AND student_type = 'F'
                AND cancelled = 0
                GROUP BY date
                ORDER BY date";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':term', $term);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return $result;
    }
    
    /**
     * Formats database results into a JSON encoded series that Flot can plot.
     * Uses the timestamp as the key and the application count as the value.
     * 
     * NB: Multiplies unix timestamp by 1000 to get a javascript timestamp (in milliseconds)
     * 
     * @param array $results
     * @return Array
     */
    private function formatForFlot(Array $results)
    {
        $resultSeries = array();
        
        foreach ($results as $row) {
            $resultSeries[] = array((int)$row['date'] * 1000, (int)$row['running_total']);
        }
        
        return json_encode($resultSeries);
    }
    
    public function getThisYearJson()
    {
        return $this->thisYearJson;
    }
    
    public function getLastYearJson()
    {
        return $this->lastYearJson;
    }
}
?>