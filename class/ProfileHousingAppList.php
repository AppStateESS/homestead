<?php

/**
 * ProfileHousingAppList - View to show the list of houing apps on the Student Profile.
 *
 * @author jbooker
 */
class ProfileHousingAppList extends View {

    private $housingApps;

    public function __construct(Array $housingApps){
        $this->housingApps = $housingApps;
    }

    public function show()
    {
        $tpl = array();

        if(empty($this->housingApps)){
            $tpl['APPLICATIONS_EMPTY'] = 'No applications found.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/profileHousingAppList.tpl');
        }
        
        // Include javascript for cancel application jquery dialog
        $jsParams = array('LINK_SELECT'=>'.cancelAppLink');
        javascript('profileCancelApplication', $jsParams, 'mod/hms/');

        $app_rows = "";

        // Show a row for each application
        foreach($this->housingApps as $app){
            $term = Term::toString($app->getTerm());
            $mealPlan = HMS_Util::formatMealOption($app->getMealPlan());
            $phone = HMS_Util::formatCellPhone($app->getCellPhone());

            $type = $app->getPrintableAppType();

            if(isset($app->room_condition)){
                $clean = $app->room_condition == 1 ? 'Neat' : 'Cluttered';
            }else{
                $clean = '';
            }

            if(isset($app->preferred_bedtime)){
                $bedtime = $app->preferred_bedtime == 1 ? 'Early' : 'Late';
            }else{
                $bedtime = '';
            }

            $viewCmd = CommandFactory::getCommand('ShowApplicationView');
            $viewCmd->setAppId($app->getId());

            if($app->isCancelled()){
                $cancelled = '(Cancelled)';
            }else{
                // Show Cancel Command
                $cancelCmd = CommandFactory::getCommand('ShowCancelHousingApplication');
                $cancelCmd->setHousingApp($app);
                $cancelled = $cancelCmd->getLink('Cancel');
            }

            $actions = '[' . $viewCmd->getLink('View') . '] ' . $cancelled;

            $app_rows[] = array('term'=>$term, 'type'=>$type, 'meal_plan'=>$mealPlan, 'cell_phone'=>$phone, 'clean'=>$clean, 'bedtime'=>$bedtime, 'actions'=>$actions);
        }

        $tpl['APPLICATIONS'] = $app_rows;


        return PHPWS_Template::process($tpl, 'hms', 'admin/profileHousingAppList.tpl');
    }
}
?>