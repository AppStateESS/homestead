<?php

/**
* Report for accessing the number of beds in each hall are internationally reserved
* for reapplying students.
*
* @author Chris Detsch
* @package HMS
*/
class InternationalReservedBeds extends Report
{

    const friendlyName = 'International Reserved Beds';
    const shortName = 'InternationalReservededBeds';

    private $term;
    private $data;
    private $total;

    public function __construct($id = 0)
    {
        parent::__construct($id);
        $data = array();
    }

    public function execute()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
        $term = $this->term;

        $db = PdoFactory::getPdoInstance();

        $query = 'SELECT * FROM hms_hall_structure
                  WHERE international_reserved = 1 AND bed_term = :term';

        $params = array('term' => $term);

        $stmt = $db->prepare($query);

        $stmt->execute($params);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $halls = array();
        $rows = array();

        foreach ($result as $node) {
            $this->total++;
            if(!in_array($node['hall_name'], $halls)) {
                $halls[] = $node['hall_name'];
            }
        }

        foreach ($halls as $hall) {
            $row = array();
            $row['HALL_NAME'] = $hall;
            $hallTotal = 0;

            foreach ($result as $bedNode) {
                if($bedNode['hall_name'] == $row['HALL_NAME']) {
                    $bed = $bedNode['bedroom_label'] . $bedNode['bed_letter'];
                    $row['beds'][] = array('ROOM_NUMBER' => $bedNode['room_number'], 'BED' => $bed);
                    $hallTotal++;
                }
            }

            $row['HALL_TOTAL'] = $hallTotal;
            $rows[] = $row;
        }

        $this->data = $rows;

    }

    /****************************
    * Accessor/Mutator Methods *
    ****************************/

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getTerm()
    {
        return $this->term;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getTotal()
    {
        return $this->total;
    }


}
