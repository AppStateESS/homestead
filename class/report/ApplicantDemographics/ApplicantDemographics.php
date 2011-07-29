<?php

/**
 * The Applicant Demographics report.
 * Gives a nice HTML table breaking down who's applied
 * by gender, studen type, and class.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 * @package hms
 */

PHPWS_Core::initModClass('hms', 'Report.php');

class ApplicantDemographics extends Report {

    const friendlyName = 'Applicant Demographics';
    
    public function __construct($id = 0){
        parent::__construct($id);
    }

    public function getFriendlyName(){
        return self::friendlyName;
    }

    public function execute()
    {
        $term           = Term::getSelectedTerm();
        $tpl['TERM']    = Term::getPrintableSelectedTerm();

        $sem = Term::getTermSem($term);

        switch($sem){
            case TERM_FALL:
                $db = new PHPWS_DB('hms_fall_application');
                $db->addJoin('LEFT OUTER', 'hms_fall_application', 'hms_new_application', 'id', 'id');
                break;
            case TERM_SPRING:
                $db = new PHPWS_DB('hms_spring_application');
                $db->addJoin('LEFT OUTER', 'hms_spring_application', 'hms_new_application', 'id', 'id');
                break;
            case TERM_SUMMER1:
            case TERM_SUMMER2:
                $db = new PHPWS_DB('hms_summer_application');
                $db->addJoin('LEFT OUTER', 'hms_summer_application', 'hms_new_application', 'id', 'id');
                break;
        }

        $db->addColumn('hms_new_application.*');
        $db->addWhere('hms_new_application.term', $term);
        $results = $db->select();

        if(PHPWS_Error::logIfError($results)) {
            PHPWS_Core::initModClass('hms', 'exception/DatabaseException.php');
            throw new DatabaseException($results->toString());
        }

        $types      = array(TYPE_FRESHMEN, TYPE_TRANSFER, TYPE_CONTINUING, TYPE_READMIT, TYPE_RETURNING, TYPE_NONDEGREE, TYPE_WITHDRAWN);
        $genders    = array(MALE, FEMALE);

        # Initalize the array for totals
        foreach($types as $init_type){
            foreach($genders as $init_gender){
                $application_totals[$init_type][$init_gender] = 0;
            }
        }

        # Calculate the totals
        foreach($results as $application){
            $application_totals[$application['student_type']][$application['gender']]++;
        }

        # Populate the template vars
        $male_sum = 0;
        foreach($types as $type){
            $tpl['male_totals'][] = array('COUNT'=>$application_totals[$type][MALE]);
            $male_sum += $application_totals[$type][MALE];
        }
        $tpl['MALE_SUM'] = $male_sum;

        $female_sum = 0;
        foreach($types as $type){
            $tpl['female_totals'][] = array('COUNT'=>$application_totals[$type][FEMALE]);
            $female_sum += $application_totals[$type][FEMALE];
        }
        $tpl['FEMALE_SUM'] = $female_sum;

        $tpl['ALL_TOTAL'] = $female_sum + $male_sum;

        $type_totals = array();
        foreach($types as $type){
            $tpl['type_totals'][] = array('COUNT'=>array_sum($application_totals[$type]));
        }

        return PHPWS_Template::process($tpl, 'hms', 'admin/reports/application_demographics.tpl');
    }

    public function getReportView()
    {

    }

    public function getSetupView(){
        return null;
    }

    public function savePeriodicData(){}
}

?>