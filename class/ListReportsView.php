<?php

PHPWS_Core::initModClass('hms', 'HMS_Util.php');

/**
 * List Reports View
 *
 * Shows a list of all the available reports and
 * the associated actions for each report. Complies the
 * list of menu items for each report by calling the ReportController's
 * getMenuItemView method.
 *
 * @author Jeremy Booker <jbooker at tux dot appstate dot edu>
 * @package HMS
*/

class ListReportsView extends hms\View{

    private $reportControllers; // Array of ReportController objects

    /**
     * Constructor
     *
     * @param Array $reportControllers The Array of report controller objets representing possible reports.
     */
    public function __construct(Array $reportControllers){
        $this->reportControllers = $reportControllers;
    }

    /**
     * Show method overridden from parent View class.
     *
     * @return String $final HTML for this output
     * @throws PermissionException
     */
    public function show()
    {
        $this->setTitle("Reports");
         
        if(!Current_User::allow('hms', 'reports')){
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to run reports.');
        }

        $tpl = array();
        $tpl['REPORTS'] = array();

        foreach($this->reportControllers as $rc) {

            $tags = array();

            $itemView = $rc->getMenuItemView();
            $tpl['REPORTS'][]['ITEM'] = $itemView->show();
        }

        $final = PHPWS_Template::process($tpl, 'hms', 'admin/display_reports.tpl');
        return $final;
    }
}

?>