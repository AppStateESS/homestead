<?php

/**
 * Contains administrative functionality
 *
 * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
 */

class HMS_Admin 
{
    var $error;

    /**
     * Constructor for HMS_Admin
     * Sets default values to NULL or empty string
     *
     * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
     */
    function HMS_Admin()
    {
        $this->error = "";
    }

    /**
     * Main method for all administrative tasks.
     * Farms tasks out to other classes.
     *
     * @author Kevin Wilcox <kevin at tux dot appstate dot edu>
     */
    function main()
    {
        if(isset($_REQUEST['type'])) {
            $type = $_REQUEST['type'];
        } else {
            $type = 'main';
        }
       
        switch($type)
        {
            case 'term':
                PHPWS_Core::initModClass('hms', 'HMS_Term.php');
                $final = HMS_Term::main();
                break;
            case 'hall':
                PHPWS_Core::initModClass('hms', 'HMS_Residence_Hall.php');
                $final = HMS_Residence_Hall::main();
                break;
            case 'floor':
                PHPWS_Core::initModClass('hms', 'HMS_Floor.php');
                $final = HMS_Floor::main();
                break;
            case 'room':
                PHPWS_Core::initModClass('hms', 'HMS_Room.php');
                $final = HMS_Room::main();
                break;
            case 'suite':
                PHPWS_Core::initModClass('hms', 'HMS_Suite.php');
                $final = HMS_Suite::main();
                break;
            case 'bed':
                PHPWS_Core::initModClass('hms', 'HMS_Bed.php');
                $final = HMS_Bed::main();
                break;
            case 'student':
                PHPWS_Core::initModClass('hms', 'HMS_Student.php');
                $final = HMS_Student::main();
                break;
            case 'maintenance':
                PHPWS_Core::initModClass('hms', 'HMS_Maintenance.php');
                $final = HMS_Maintenance::main();
                break;
            case 'display':
                PHPWS_Core::initModClass('hms', 'HMS_Display.php');
                $final = HMS_Display::main();
                break;
            case 'assignment':
                PHPWS_Core::initModClass('hms', 'HMS_Assignment.php');
                $final = HMS_Assignment::main();
                break;
            case 'rlc':
                PHPWS_Core::initModClass('hms', 'HMS_Learning_Community.php');
                $final = HMS_Learning_Community::main();
                break;
            case 'admin':
                switch($_REQUEST['op'])
                {
                    case 'show_primary_admin_panel':
                        $final = HMS_Admin::show_primary_admin_panel();
                        break;
                    default:
                        PHPWS_Core::initModClass('hms', 'HMS_Display.php');
                        $final = HMS_Display::main();
                        break;
                }
                break;
            case 'xml':
                PHPWS_Core::initModClass('hms','HMS_XML.php');
                $final = HMS_XML::main();
                break;
            case 'roommate':
                PHPWS_Core::initModClass('hms', 'HMS_Roommate.php');
                $final = HMS_Roommate::main();
                break;
            case 'roommate_approval':
                PHPWS_Core::initModClass('hms', 'HMS_Roommate_Approval.php');
                $final = HMS_Roommate_Approval::main();
                break;
            case 'reports':
                PHPWS_Core::initModClass('hms', 'HMS_Reports.php');
                $final = HMS_Reports::main();
                break;
            case 'autoassign':
                PHPWS_Core::initModClass('hms', 'HMS_Pending_Assignment.php');
                $final = HMS_Pending_Assignment::main();
                break;
            case 'letter':
                PHPWS_Core::initModClass('hms', 'HMS_Letter.php');
                $final = HMS_Letter::main();
                break;
            case 'queue':
                $result = HMS_Admin::handle_queues();
                if($result !== TRUE) {
                    $final = $result;
                    break;
                }
                PHPWS_Core::initModClass('hms', 'HMS_Maintenance.php');
                $_REQUEST['op'] = 'show_maintenance_options';
                $final = HMS_Maintenance::main();
                break;
            case 'movein':
                PHPWS_Core::initModClass('hms', 'HMS_Movein_Time.php');
                $final = HMS_Movein_Time::main();
                break;
            case 'deadlines':
                PHPWS_Core::initModClass('hms', 'HMS_Deadlines.php');
                $final = HMS_Deadlines::main();
                break;
            default:
                PHPWS_Core::initModClass('hms', 'HMS_Maintenance.php');
                $_REQUEST['op'] = 'show_maintenance_options';
                $final = HMS_Maintenance::main();
                break;
        }

        Layout::add($final);

        $links[] = PHPWS_Text::secureLink(_('HMS Statistics'), 'hms', array('type'=>'display', 'op'=>'display_system_statistics'));
        $links[] = PHPWS_Text::secureLink(_('HMS Reports'), 'hms', array('type'=>'reports', 'op'=>'display_reports'));
        //$links[] = PHPWS_Text::secureLink(_('Search Halls'), 'hms', array('type'=>'hall', 'op'=>'search_residence_halls'));
        $links[] = PHPWS_Text::secureLink(_('Search Students'), 'hms', array('type'=>'student', 'op'=>'enter_student_search_data'));
        if(Current_User::allow('hms', 'maintenance') || Current_User::allow('hms', 'admin')) 
            $links[] = PHPWS_Text::secureLink(_('Comprehensive Maintenance'), 'hms', array('type'=>'maintenance', 'op'=>'show_maintenance_options'));
        if(Current_User::allow('hms', 'primary_admin_panel') || Current_User::allow('hms', 'admin')) 
            $links[] = PHPWS_Text::secureLink(_('Main Panel'), 'hms', array('type'=>'admin', 'op'=>'show_primary_admin_panel'));
        $links[] = PHPWS_Text::secureLink(_('Logout'), 'users', array('action'=>'user', 'command'=>'logout'));

        MiniAdmin::add('hms', $links);
    }

    function handle_queues()
    {
        $op = $_REQUEST['op'];
        if($_REQUEST['queue'] == 'assign') {
                PHPWS_Core::initModClass('hms', 'HMS_Process_Assign_Unit.php');
                if($op == 'enable') {
                    HMS_Process_Unit::enable_assign_queue();
                } else if($op == 'disable') {
                    HMS_Process_Unit::disable_assign_queue();
                } else {
                    return "Unrecognized Op $op";
                }
        } else {
                return "Unrecognized Queue {$_REQUEST['queue']}";
        }

        return TRUE;
    }

    /**
     * Display the admin panel requested by Housing 
     */
    function show_primary_admin_panel()
    {
        PHPWS_Core::initModClass('hms', 'HMS_Forms.php');
        return HMS_Form::show_primary_admin_panel();
    }
}

?>
