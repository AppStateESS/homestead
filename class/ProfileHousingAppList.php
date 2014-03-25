<?php

/**
 * ProfileHousingAppList - View to show the list of houing apps on the Student Profile.
 *
 * @author jbooker
 */
class ProfileHousingAppList extends hms\View{

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

        // Get the list of cancellation reasons
        $reasons = HousingApplication::getCancellationReasons();
        
        // Show a row for each application
        foreach($this->housingApps as $app){
            $term = Term::toString($app->getTerm());
            $mealPlan = HMS_Util::formatMealOption($app->getMealPlan());
            $phone = HMS_Util::formatCellPhone($app->getCellPhone());

            $type = $app->getPrintableAppType();

            // Clean/dirty and early/late preferences are only fields on the FallApplication
            if($app instanceof FallApplication && isset($app->room_condition)){
                $clean = $app->room_condition == 1 ? 'Neat' : 'Cluttered';
            }else{
                $clean = '';
            }

            if($app instanceof FallApplication && isset($app->preferred_bedtime)){
                $bedtime = $app->preferred_bedtime == 1 ? 'Early' : 'Late';
            }else{
                $bedtime = '';
            }

            $viewCmd = CommandFactory::getCommand('ShowApplicationView');
            $viewCmd->setAppId($app->getId());

            $rowStyle = "";
            
            if($app->isCancelled()){
                $cancelled = "({$reasons[$app->getCancelledReason()]})";
                $rowStyle = 'disabledText';
            }else{
                // Show Cancel Command, if user has permission to cancel apps
                if(Current_User::allow('hms', 'cancel_housing_application')){
                    $cancelCmd = CommandFactory::getCommand('ShowCancelHousingApplication');
                    $cancelCmd->setHousingApp($app);
                    $cancelled = '[' . $cancelCmd->getLink('Cancel') . ']';
                }else{
                    $cancelled = '';
                }
            }

            $actions = '[' . $viewCmd->getLink('View') . '] ' . $cancelled;

            $app_rows[] = array('term'=>$term, 'type'=>$type, 'meal_plan'=>$mealPlan, 'cell_phone'=>$phone, 'clean'=>$clean, 'bedtime'=>$bedtime, 'actions'=>$actions, 'row_style'=>$rowStyle);
        }

        $tpl['APPLICATIONS'] = $app_rows;


        return PHPWS_Template::process($tpl, 'hms', 'admin/profileHousingAppList.tpl');
    }
}
?>
