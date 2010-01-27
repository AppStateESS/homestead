<?php

/**
 * Description
 * @author Jeff Tickle <jtickle at tux dot appstate dot edu>
 */

PHPWS_Core::initModClass('pulse', 'ScheduledPulse.php');

class ScheduledLottery extends ScheduledPulse
{
    public function __construct($id = NULL)
    {
        $this->module = 'hms';
        $this->class_file = 'ScheduledLottery.php';
        $this->class = 'ScheduledLottery';

        parent::__construct($id);
    }

    public function execute()
    {
        PHPWS_Core::initModClass('hms', 'HMS.php');

        // Copied and pasted from index.php
        require_once(PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php');

        // Copied and pasted from ExecuteLotteryCommand.php
        PHPWS_Core::initModClass('hms', 'HMS_Lottery.php');
        HMS_Lottery::runLottery();

        $now = time();
        $date = date('m/d/Y H:i:s', $now);

        if($_SESSION['UNSCHEDULE_LOTTERY']) {
            echo "Lottery has executed.  The time is $date.  Lottery asked to be unscheduled.\n";
        } else {
            $hr = date('H', $now);
            $day = date('d', $now);

            if($hr >= 9 && $hr < 16) {
                $then = strtotime("16:00:00", $now);
            } else {
                $then = strtotime("+1 day 09:00:00", $this->execute_after);
            }

            $newdate = date('m/d/Y H:i:s', $then);

            echo "Lottery will now execute.  The time is $date.  Lottery has been scheduled to run at $newdate.\n";

            $sp = $this->makeClone();
            $sp->execute_after = $then;
            $sp->save();
        }

        return TRUE;
    }
}

?>
