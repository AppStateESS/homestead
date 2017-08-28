<?php

namespace Homestead\report\CancelledAppsContinuingGraph;

/**
 * Main report class for the Cancelled Applications for continuing students over time report.
 *
 * @author Jeremy Booker
 * @package Hms
 */
class CancelledAppsContinuingGraph extends Report {

    const friendlyName = 'Cancelled Application Graph (Continuing)';
    const shortName = 'CancelledAppsContinuingGraph';

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
        // If the report is for the fall, we want continuing students with
        // application terms <= the spring term.
        if (Term::getTermSem($term) == TERM_FALL) {
            $year = Term::getTermYear($term);
            $applicationTerm = $year . TERM_SPRING;
        } else {
            // For any other term, we want the application term <= the previous term
            $applicationTerm = Term::getPrevTerm($term);
        }

        PHPWS_Core::initModClass('hms', 'PdoFactory.php');
        $db = PdoFactory::getInstance()->getPdo();

        $query ="SELECT
                date_part('epoch', date_trunc('day',timestamp 'epoch' + cancelled_on * interval '1 second')) as date,
                SUM(COUNT(cancelled_on)) OVER (ORDER BY date_part('epoch', date_trunc('day',timestamp 'epoch' + cancelled_on * interval '1 second'))) as running_total
                FROM hms_new_application
                WHERE term = :term
                and application_term <= $applicationTerm
                and cancelled = 1
                and cancelled_reason NOT IN ('offer_made', 'before_assignment')
                GROUP BY date
                ORDER BY date;";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':term', $term);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

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
