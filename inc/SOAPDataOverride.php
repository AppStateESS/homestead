<?php

class SOAPDataOverride {

    private static function applyExceptions(&$student)
    {
        /*
         * This is a hack to fix some freshmen students who have application terms in the future but are considered type 'C' by the registrar's office.
         * See Trac #719
         */
        PHPWS_Core::initModClass('hms', 'Term.php');
        if ($student->getApplicationTerm() > Term::getCurrentTerm() && $student->getType() == TYPE_CONTINUING) {
            $student->setType(TYPE_FRESHMEN);
        }

        // This is a hack to fix the student type for international grad students
        $type = $student->getType();
        if ((!isset($type) || $type == '') && $student->getStudentLevel() == LEVEL_GRAD && $student->isInternational() == 1) {
            $student->setType(TYPE_GRADUATE);
            $student->setClass(CLASS_SENIOR);
        }
    }
}

?>
