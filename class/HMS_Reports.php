<?php

class HMS_Reports{

    function display_reports()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::display_reports();
    }

    function run_report()
    {
        switch($_REQUEST['reports'])
        {
            case 'housing_apps':
                return HMS_Reports::run_applicant_demographics_report();
                break;
            case 'housing_asss':
                return HMS_Reports::run_housing_demographics_report();
                break;
            default:
                return "ugh";
                break;
        }
    }

    function run_housing_demographics_report()
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        $db = &new PHPWS_DB('hms_residence_hall');
        $db->addColumn('id');
        $db->addColumn('hall_name');
        $db->addWhere('deleted', '0');
        $db->addOrder('hall_name', 'asc');
        $result = $db->select();
        
        foreach($result as $line) {
            $db = &new PHPWS_DB('hms_assignment');
            $db->addColumn('hms_assignment.asu_username');
            $db->addWhere('bed_id', 'hms_beds.id');
            $db->addWhere('hms_beds.bedroom_id', 'hms_bedrooms.id');
            $db->addWhere('hms_bedrooms.room_id', 'hms_room.id');
            $db->addWhere('hms_room.floor_id', 'hms_floor.id');
            $db->addWhere('hms_floor.building', 'hms_residence_hall.id');
            $db->addWhere('hms_residence_hall.id', $line['id']);
            $stuffs = $db->select();
            foreach($stuffs as $stuff) {
                $person = HMS_SOAP::get_gender_class($stuff['asu_username']);
                $g = $person['gender'];
                $c = $person['class'];

                if(isset($building[$line['hall_name']][$c][$g])) {
                    $building[$line['hall_name']][$c][$g]++;
                } else {
                    $building[$line['hall_name']][$c][$g] = 1;
                }
            }
        }

        $total_frm = 0;
        $total_frf = 0;
        $total_som = 0;
        $total_sof = 0;
        $total_jrm = 0;
        $total_jrf = 0;
        $total_srm = 0;
        $total_srf = 0;

        $content = '';

        foreach($building as $hall) {
            ksort($hall);
            $name = key($building);
            $content .= "$name<br /> ";
            $content .= "New Freshman <br /> ";
            $content .= "Male: " . $building[$name]["NFR"]["M"] . "<br /> ";
            $content .= "Female: " . $building[$name]["NFR"]["F"] . "<br /> ";
            $content .= "<br /> ";
            $content .= "Freshman <br /> ";
            $content .= "Male: " . $building[$name]["FR"]["M"] . "<br /> ";
            $content .= "Female: " . $building[$name]["FR"]["F"] . "<br /> ";
            $content .= "<br /> ";
            $content .= "Sophomore <br /> ";
            $content .= "Male: " . $building[$name]["SO"]["M"] . "<br /> ";
            $content .= "Female: " . $building[$name]["SO"]["F"] . "<br /> ";
            $content .= "<br /> ";
            $content .= "Junior <br /> ";
            $content .= "Male: " . $building[$name]["JR"]["M"] . "<br /> ";
            $content .= "Female: " . $building[$name]["JR"]["F"] . "<br /> ";
            $content .= "<br /> ";
            $content .= "Senior <br /> ";
            $content .= "Male: " . $building[$name]["SR"]["M"] . "<br /> ";
            $content .= "Female: " . $building[$name]["SR"]["F"] . "<br /> ";
            $content .= "<br /> ";
            $content .= "==============================<br /> ";
            $content .= "<br />";
            $total_nfrm += $building[$name]["NFR"]["M"];
            $total_nfrf += $building[$name]["NFR"]["F"];
            $total_frm += $building[$name]["FR"]["M"];
            $total_frf += $building[$name]["FR"]["F"];
            $total_som += $building[$name]["SO"]["M"];
            $total_sof += $building[$name]["SO"]["F"];
            $total_jrm += $building[$name]["JR"]["M"];
            $total_jrf += $building[$name]["JR"]["F"];
            $total_srm += $building[$name]["SR"]["M"];
            $total_srf += $building[$name]["SR"]["F"];
            next($building);
        }

        $content .=  "New Freshmen Male: $total_nfrm<br /> ";
        $content .=  "New Freshmen Female: $total_nfrf<br /> ";
        $content .=  "Freshmen Male: $total_frm<br /> ";
        $content .=  "Freshmen Female: $total_frf<br /> ";
        $content .=  "Sophomore Male: $total_som<br /> ";
        $content .=  "Sophomore Female: $total_sof<br /> ";
        $content .=  "Junior Male: $total_jrm<br /> ";
        $content .=  "Junior Female: $total_jrf<br /> ";
        $content .=  "Senior Male: $total_srm<br /> ";
        $content .=  "Senior Female: $total_srf<br /> ";
        $content .=  "<br /> ";
        $content .=  "<br /> ";

        return $content;
    }

    function run_applicant_demographics_report()
    {
        PHPWS_Core::initModClass('hms', 'HMS_SOAP.php');

        $db = &new PHPWS_DB('hms_application');
        $db->addColumn('hms_student_id');
        $db->addWhere('deleted', '0');
        $db->addOrder('hms_student_id', 'ASC');
        $results = $db->select();
        $content = '';

        foreach($results as $line) {
            $person = HMS_SOAP::get_gender_class($line['hms_student_id']);
            $g = $person['gender'];
            $c = $person['class'];

            if(isset($application[$c][$g])) {
                $application[$c][$g]++;
            } else {
                $application[$c][$g] = 1;
            }
        }

        $content .= "Housing Applications received by class and gender:<br /><br />";
        $content .= "New Freshman <br />";
        $content .= "Male: " . $application["NFR"]["M"] . "<br />";
        $content .= "Female: " . $application["NFR"]["F"] . "<br />";
        $content .= "<br />**Note: New Freshmen are classified as any freshman with 0 completed credit hours at Appalachian State University**<br />\n";
        $content .= "<br />";
        $content .= "Freshmen <br />";
        $content .= "Male: " . $application["FR"]["M"] . "<br />";
        $content .= "Female: " . $application["FR"]["M"] . "<br />";
        $content .= "<br />";
        $content .= "Sophomore <br />";
        $content .= "Male: " . $application["SO"]["M"] . "<br />";
        $content .= "Female: " . $application["SO"]["F"] . "<br />";
        $content .= "<br />";
        $content .= "Junior <br />";
        $content .= "Male: " . $application["JR"]["M"] . "<br />";
        $content .= "Female: " . $application["JR"]["F"] . "<br />";
        $content .= "<br />";
        $content .= "Senior <br />";
        $content .= "Male: " . $application["SR"]["M"] . "<br />";
        $content .= "Female: " . $application["SR"]["F"] . "<br />";
        $content .= "<br />";
        $content .= "<br />";
    
        return $content;
    }

    function main(){
        $op = $_REQUEST['op'];
        switch($op){
            case 'display_reports':
                return HMS_Reports::display_reports();
                break;
            case 'run_report':
                return HMS_Reports::run_report();
                break;
            default:
                # No such 'op', or no 'op' specified
                # TODO: Find a way to throw an error here
                return $op;
                break;
        }
    }
}

?>
