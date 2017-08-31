<?php

namespace Homestead\Report\AssignmentsByType;

use \Homestead\Report;
use \Homestead\PdoFactory;

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
        $pdo = PdoFactory::getPdoInstance();

        $sql = "select reason, count(*) from hms_assignment where term = :term group by reason order by reason";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(array('term'=>$this->term));

        $this->typeCounts = $stmt->fetchAll(\PDO::FETCH_ASSOC);
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
