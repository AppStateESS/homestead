<?php

/*
 *
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/gpl-3.0.html
 */

/**
 * Description of MoveInTimes
 *
 * @author matt
 */
class MoveInTimes extends Report {
    const friendlyName = 'Move In Times';
    const shortName = 'MoveInTimes';

    private $term;
    private $rows;
    private $problems;

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
        PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
        PHPWS_Core::initModClass('hms', 'HMS_Movein_Time.php');
        PHPWS_Core::initModClass('hms', 'HMS_Util.php');

        if (!isset($this->term) || is_null($this->term)) {
            throw new InvalidArgumentException('Missing term.');
        }

        $term = Term::getTermSem($this->term);

        $halls = HMS_Residence_Hall::get_halls($this->term);

        foreach ($halls as $hall) {

            $floors = $hall->get_floors();

            $floor_array = array();
            foreach ($floors as $floor) {

                if (is_null($floor->f_movein_time_id)) {
                    $f_time = 'None';
                } else {
                    $f_movein = new HMS_Movein_Time($floor->f_movein_time_id);
                    $f_time = $f_movein->get_formatted_begin_end();
                }

                if (is_null($floor->t_movein_time_id)) {
                    $t_time = 'None';
                } else {
                    $t_movein = new HMS_Movein_Time($floor->t_movein_time_id);
                    $t_time = $t_movein->get_formatted_begin_end();
                }

                if (is_null($floor->rt_movein_time_id)) {
                    $rt_time = 'None';
                } else {
                    $rt_movein = new HMS_Movein_Time($floor->rt_movein_time_id);
                    $rt_time = $rt_movein->get_formatted_begin_end();
                }

                $floor_array[] = array('FLOOR_NUM' => $floor->floor_number,
                    'F_TIME' => $f_time,
                    'T_TIME' => $t_time,
                    'RT_TIME' => $rt_time);
            }

            $this->rows[] = array('HALL_NAME' => $hall->hall_name, 'floor_rows' => $floor_array);
        }
    }

    public function getRows()
    {
        return $this->rows;
    }

}


