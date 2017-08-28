<?php

namespace Homestead\report\HallOccupancy;

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
        if (!isset($this->term) || is_null($this->term)) {
            throw new \InvalidArgumentException('Missing term.');
        }

        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');

        $halls = ResidenceHallFactory::getHallsForTerm($this->term);

        // accumulatrs for totaling beds across all halls
        $totalBeds = 0;
        $totalVacantBeds = 0;

        $hallArray = array();

        foreach ($halls as $hall) {

            $bedsByHall = 0;
            $vacantBedsByHall = 0;

            $floorArray = array();

            $floors = $hall->get_floors();
            if ($floors == NULL) {
                continue;
            }
            foreach ($floors as $floor) {
                $vacantBedsByFloor = 0;
                $totalBedsByFloor = 0;

                if ($floor->is_online == 0) {
                    $floorArray[$floor->floor_number]['floor_number'] = $floor->floor_number . ' - Offline';
                    $floorArray[$floor->floor_number]['vacancies_by_floor'] = null;
                    $floorArray[$floor->floor_number]['total_beds_by_floor'] = null;
                    continue;
                }


                $rooms = $floor->get_rooms();
                if ($rooms == NULL) {
                    continue;
                }
                foreach ($rooms as $room) {
                    if ($room->offline == 1) {
                        continue;
                    }
                    $beds = $room->get_beds();
                    if (!empty($beds)) {
                        foreach ($beds as $bed) {
                            $bedsByHall++;
                            $totalBedsByFloor++;
                            $totalBeds++;
                            if ($bed->has_vacancy()) {
                                $totalVacantBeds++;
                                $vacantBedsByHall++;
                                $vacantBedsByFloor++;
                            }
                        }
                    }
                }
                $floorArray[$floor->floor_number]['floor_number'] = $floor->floor_number;
                $floorArray[$floor->floor_number]['vacancies_by_floor'] = $vacantBedsByFloor;
                $floorArray[$floor->floor_number]['total_beds_by_floor'] = $totalBedsByFloor;
            }
            $hallArray[$hall->hall_name]['hall_name'] = $hall->hall_name;
            $hallArray[$hall->hall_name]['hall_vacancies'] = $vacantBedsByHall;
            $hallArray[$hall->hall_name]['hall_total_beds'] = $bedsByHall;
            ksort($floorArray);
            $hallArray[$hall->hall_name]['floor_rows'] = $floorArray;
        }
        $this->rows = array('total_beds' => $totalBeds, 'vacant_beds' => $totalVacantBeds, 'hall_rows' => $hallArray);
    }

    public function getRows()
    {
        return $this->rows;
    }

}
