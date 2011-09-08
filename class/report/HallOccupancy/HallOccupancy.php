<?php

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

/**
 * Description of HallOccupancy
 *
 * @author matt
 */
class HallOccupancy extends Report {
    const friendlyName = 'Hall Occupancy';
    const shortName = 'HallOccupancy';

    private $term;
    private $rows;
    private $problems;

    public function __construct($id=0)
    {
        parent::__construct($id);
    }

    public function setTerm($term)
    {
        $this->term = $term;
    }

    public function getTerm()
    {
        return $this->term;
    }

    public function execute()
    {

    }

    public function getRows()
    {
        return $this->rows;
    }

}

?>
