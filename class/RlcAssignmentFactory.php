<?php

namespace Homestead;

class RlcAssignmentFactory {

    public static function getAssignmentsByTermStateType($term, $state, $type)
    {
        $db = new \PHPWS_DB('hms_learning_community_applications');
        $db->addColumn('hms_learning_community_applications.*');
        $db->addColumn('hms_learning_community_assignment.*');
        $db->addJoin('', 'hms_learning_community_applications', 'hms_learning_community_assignment', 'id', 'application_id');

        $db->addWhere('term', $term);
        $db->addWhere('hms_learning_community_assignment.state', $state);
        $db->addWhere('application_type', $type);

        return $db->getObjects('HMS_RLC_Assignment');
    }
}
