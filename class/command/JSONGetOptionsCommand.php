<?php

/**
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */
class JSONGetOptionsCommand
{

    public function getRequestVars()
    {
        return array('action' => 'JSONGetOptions');
    }

    public function execute(CommandContext $context)
    {
        $options = array();

        $options['meal_plan'] = array(
        array('id' => BANNER_MEAL_LOW, 'value' => 'Low'),
        array('id' => BANNER_MEAL_STD, 'value' => 'Standard'),
        array('id' => BANNER_MEAL_HIGH, 'value' => 'High'),
        array('id' => BANNER_MEAL_SUPER, 'value' => 'Super'),
        array('id' => BANNER_MEAL_NONE, 'value' => 'None'),
        array('id' => BANNER_MEAL_5WEEK, 'value' => 'Summer (5 weeks)'));

        $options['assignment_type'] = array(
        array('id' => ASSIGN_ADMIN, 'value' => 'Administrative'),
        array('id' => ASSIGN_APPEALS, 'value' => 'Appeals'),
        array('id' => ASSIGN_LOTTERY, 'value' => 'Lottery'),
        array('id' => ASSIGN_FR, 'value' => 'Freshmen'),
        array('id' => ASSIGN_TRANSFER, 'value' => 'Transfer'),
        array('id' => ASSIGN_APH, 'value' => 'APH'),
        array('id' => ASSIGN_RLC_FRESHMEN, 'value' => 'RLC Freshmen'),
        array('id' => ASSIGN_RLC_TRANSFER, 'value' => 'RLC Transfer'),
        array('id' => ASSIGN_RLC_CONTINUING, 'value' => 'RLC Continuing'),
        array('id' => ASSIGN_HONORS_FRESHMEN, 'value' => 'Honors Freshmen'),
        array('id' => ASSIGN_HONORS_CONTINUING, 'value' => 'Honors Continuing'),
        array('id' => ASSIGN_LLC_FRESHMEN, 'value' => 'LLC Freshmen'),
        array('id' => ASSIGN_LLC_CONTINUING, 'value' => 'LLC Continuing'),
        array('id' => ASSIGN_INTL, 'value' => 'International'),
        array('id' => ASSIGN_RA, 'value' => 'RA'),
        array('id' => ASSIGN_RA_ROOMMATE, 'value' => 'RA Roommate'),
        array('id' => ASSIGN_MEDICAL_FRESHMEN, 'value' => 'Medical Freshmen'),
        array('id' => ASSIGN_MEDICAL_CONTINUING, 'value' => 'Medical Continuing'),
        //ASSIGN_MEDICAL               => 'Medical'),
        array('id' => ASSIGN_SPECIAL_FRESHMEN, 'value' => 'Special Needs Freshmen'),
        array('id' => ASSIGN_SEPCIAL_CONTINUING, 'value' => 'Special Needs Continuing'),
        //ASSIGN_SPECIAL               => 'Special Needs'),
        array('id' => ASSIGN_RHA, 'value' => 'RHA/NRHH'),
        array('id' => ASSIGN_SCHOLARS, 'value' => 'Diversity &amp; Plemmons Scholars'));

        $options['default_plan'] = BANNER_MEAL_STD;
        $options['default_assignment'] = ASSIGN_ADMIN;

        $context->setContent(json_encode($options));
    }

}
