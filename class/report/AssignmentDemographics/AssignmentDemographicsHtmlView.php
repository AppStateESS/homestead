<?php

namespace Homestead\report\AssignmentDemographics;

class AssignmentDemographicsHtmlView extends ReportHtmlView {

    protected function render()
    {
        parent::render();

        // term
        $this->tpl['TERM'] = Term::toString($this->report->getTerm());

        /******************
         * Hall summaries *
        */
        $hallSummaries = $this->report->getHallSummaries();

        $summaries = array();

        foreach($hallSummaries as $hallName=>$sum){
            $tplRow = array();

            $tplRow['HALL_NAME'] = $hallName;

            // Males
            $tplRow['BLG_F_FR_MALE'] = $sum[TYPE_FRESHMEN][CLASS_FRESHMEN][MALE];

            $tplRow['BLG_T_FR_MALE'] = $sum[TYPE_TRANSFER][CLASS_FRESHMEN][MALE];
            $tplRow['BLG_T_SO_MALE'] = $sum[TYPE_TRANSFER][CLASS_SOPHOMORE][MALE];
            $tplRow['BLG_T_JR_MALE'] = $sum[TYPE_TRANSFER][CLASS_JUNIOR][MALE];
            $tplRow['BLG_T_SR_MALE'] = $sum[TYPE_TRANSFER][CLASS_SENIOR][MALE];

            $tplRow['BLG_C_FR_MALE'] = $sum[TYPE_CONTINUING][CLASS_FRESHMEN][MALE];
            $tplRow['BLG_C_SO_MALE'] = $sum[TYPE_CONTINUING][CLASS_SOPHOMORE][MALE];
            $tplRow['BLG_C_JR_MALE'] = $sum[TYPE_CONTINUING][CLASS_JUNIOR][MALE];
            $tplRow['BLG_C_SR_MALE'] = $sum[TYPE_CONTINUING][CLASS_SENIOR][MALE];

            // Females
            $tplRow['BLG_F_FR_FEMALE'] = $sum[TYPE_FRESHMEN][CLASS_FRESHMEN][FEMALE];

            $tplRow['BLG_T_FR_FEMALE'] = $sum[TYPE_TRANSFER][CLASS_FRESHMEN][FEMALE];
            $tplRow['BLG_T_SO_FEMALE'] = $sum[TYPE_TRANSFER][CLASS_SOPHOMORE][FEMALE];
            $tplRow['BLG_T_JR_FEMALE'] = $sum[TYPE_TRANSFER][CLASS_JUNIOR][FEMALE];
            $tplRow['BLG_T_SR_FEMALE'] = $sum[TYPE_TRANSFER][CLASS_SENIOR][FEMALE];

            $tplRow['BLG_C_FR_FEMALE'] = $sum[TYPE_CONTINUING][CLASS_FRESHMEN][FEMALE];
            $tplRow['BLG_C_SO_FEMALE'] = $sum[TYPE_CONTINUING][CLASS_SOPHOMORE][FEMALE];
            $tplRow['BLG_C_JR_FEMALE'] = $sum[TYPE_CONTINUING][CLASS_JUNIOR][FEMALE];
            $tplRow['BLG_C_SR_FEMALE'] = $sum[TYPE_CONTINUING][CLASS_SENIOR][FEMALE];

            $byGender = array();
            $byType = array();

            $byGender[MALE]   = 0;
            $byGender[FEMALE] = 0;

            $byType[TYPE_FRESHMEN]   = 0;
            $byType[TYPE_TRANSFER]   = 0;
            $byType[TYPE_CONTINUING] = 0;

            foreach($sum as $type=>$t){
                if($type == 'OTHER'){
                    continue;
                }
                foreach($t as $class=>$c){
                    foreach($c as $gender=>$g){
                        $byGender[$gender] += $g;
                        $byType[$type] += $g;
                    }
                }
            }

            // totals by gender
            $tplRow['BLG_TOTAL_MALES']   = $byGender[MALE];
            $tplRow['BLG_TOTAL_FEMALES'] = $byGender[FEMALE];

            // totals by types
            $tplRow['BLG_TOTAL_F'] = $byType[TYPE_FRESHMEN];
            $tplRow['BLG_TOTAL_T'] = $byType[TYPE_TRANSFER];
            $tplRow['BLG_TOTAL_C'] = $byType[TYPE_CONTINUING];

            $tplRow['BLG_TOTAL'] = $byType[TYPE_FRESHMEN] + $byType[TYPE_TRANSFER] + $byType[TYPE_CONTINUING] + $sum['OTHER'];

            $tplRow['BLG_OTHER'] = $sum['OTHER'];

            $summaries[] = $tplRow;
        }

        // Use row-repeats to show all those summaries
        $this->tpl['summaries'] = $summaries;

        /************************
         * Grand totals
        */

        $grandTotals = $this->report->getGrandTotals();
        $genderTotals = $this->report->getGrandTotalsByGender();
        $typeTotals = $this->report->getGrandTotalsByType();

        // Males
        $this->tpl['TOTAL_F_FR_MALE'] = $grandTotals[TYPE_FRESHMEN][CLASS_FRESHMEN][MALE];

        $this->tpl['TOTAL_T_FR_MALE'] = $grandTotals[TYPE_TRANSFER][CLASS_FRESHMEN][MALE];
        $this->tpl['TOTAL_T_SO_MALE'] = $grandTotals[TYPE_TRANSFER][CLASS_SOPHOMORE][MALE];
        $this->tpl['TOTAL_T_JR_MALE'] = $grandTotals[TYPE_TRANSFER][CLASS_JUNIOR][MALE];
        $this->tpl['TOTAL_T_SR_MALE'] = $grandTotals[TYPE_TRANSFER][CLASS_SENIOR][MALE];

        $this->tpl['TOTAL_C_FR_MALE'] = $grandTotals[TYPE_CONTINUING][CLASS_FRESHMEN][MALE];
        $this->tpl['TOTAL_C_SO_MALE'] = $grandTotals[TYPE_CONTINUING][CLASS_SOPHOMORE][MALE];
        $this->tpl['TOTAL_C_JR_MALE'] = $grandTotals[TYPE_CONTINUING][CLASS_JUNIOR][MALE];
        $this->tpl['TOTAL_C_SR_MALE'] = $grandTotals[TYPE_CONTINUING][CLASS_SENIOR][MALE];

        // Females
        $this->tpl['TOTAL_F_FR_FEMALE'] = $grandTotals[TYPE_FRESHMEN][CLASS_FRESHMEN][FEMALE];

        $this->tpl['TOTAL_T_FR_FEMALE'] = $grandTotals[TYPE_TRANSFER][CLASS_FRESHMEN][FEMALE];
        $this->tpl['TOTAL_T_SO_FEMALE'] = $grandTotals[TYPE_TRANSFER][CLASS_SOPHOMORE][FEMALE];
        $this->tpl['TOTAL_T_JR_FEMALE'] = $grandTotals[TYPE_TRANSFER][CLASS_JUNIOR][FEMALE];
        $this->tpl['TOTAL_T_SR_FEMALE'] = $grandTotals[TYPE_TRANSFER][CLASS_SENIOR][FEMALE];

        $this->tpl['TOTAL_C_FR_FEMALE'] = $grandTotals[TYPE_CONTINUING][CLASS_FRESHMEN][FEMALE];
        $this->tpl['TOTAL_C_SO_FEMALE'] = $grandTotals[TYPE_CONTINUING][CLASS_SOPHOMORE][FEMALE];
        $this->tpl['TOTAL_C_JR_FEMALE'] = $grandTotals[TYPE_CONTINUING][CLASS_JUNIOR][FEMALE];
        $this->tpl['TOTAL_C_SR_FEMALE'] = $grandTotals[TYPE_CONTINUING][CLASS_SENIOR][FEMALE];

        // totals by gender
        $this->tpl['TOTAL_TOTAL_MALES']   = $genderTotals[MALE];
        $this->tpl['TOTAL_TOTAL_FEMALES'] = $genderTotals[FEMALE];

        // totals by types
        $this->tpl['TOTAL_TOTAL_F'] = $typeTotals[TYPE_FRESHMEN];
        $this->tpl['TOTAL_TOTAL_T'] = $typeTotals[TYPE_TRANSFER];
        $this->tpl['TOTAL_TOTAL_C'] = $typeTotals[TYPE_CONTINUING];
        $this->tpl['TOTAL_OTHER'] = $typeTotals['OTHER'];

        $this->tpl['TOTAL_TOTAL'] = $genderTotals[MALE] + $genderTotals[FEMALE] + $typeTotals['OTHER'];

        // Problems....
        $problems = $this->report->getProblemsList();
        foreach($problems as $prob) {
        	$row = array('DESC' => $prob);
        	$this->tpl['problems'][] = $row;
        }

        return PHPWS_Template::process($this->tpl, 'hms', 'admin/reports/AssignmentDemographics.tpl');
    }
}
