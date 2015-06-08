<?php

require_once PHPWS_SOURCE_DIR . 'mod/hms/inc/defines.php';

class AutoassignPulse
{
    public function __construct($id = NULL)
    {
        $this->module = 'hms';
        $this->class_file = 'AutoassignPulse.php';
        $this->class = 'AutoassignPulse';

        parent::__construct($id);
    }

    public static function execute()
    {
        PHPWS_Core::initModClass('hms', 'HMS.php');
        PHPWS_Core::initModClass('hms', 'Autoassigner.php');

        ob_start();

        echo "<html><head><title>AUTOASSIGNER - SCHEDULED BY PULSE</title></head><body><pre>\n\n";
        echo "AUTOASSIGNER 1970s MODE\n\n";

        try {
            $assigner = new Autoassigner(Term::getSelectedTerm());
            $assigner->autoassign();
        } catch(Exception $e) {
            echo "EXCEPTION CAUGHT: " . $e->getMessage();
        }

        echo "</pre></body></html>\n\n";

        $message = ob_get_contents();
        ob_end_clean();

        // TODO: Email the person that scheduled the autoassign.
        mail(HMS_AUTOASSIGN_EMAIL, 'Autoassign Complete', $message);

        return TRUE;
    }
}


