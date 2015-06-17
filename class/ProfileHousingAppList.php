<?php

/**
 * ProfileHousingAppList - View to show the list of houing apps on the Student Profile.
 *
 * @author jbooker
 */
class ProfileHousingAppList extends hms\View
{
    private $housingApps;

    public function __construct(Array $housingApps)
    {
        $this->housingApps = $housingApps;
    }

    public function show()
    {
        $tpl = array();

        if (empty($this->housingApps)) {
            $tpl['APPLICATIONS_EMPTY'] = 'No applications found.';
            return PHPWS_Template::process($tpl, 'hms', 'admin/profileHousingAppList.tpl');
        }

        // Include javascript for cancel application jquery dialog
        $jsParams = array('LINK_SELECT' => '.cancelAppLink');
        javascript('profileCancelApplication', $jsParams, 'mod/hms/');

        $app_rows = "";

        // Get the list of cancellation reasons
        $reasons = HousingApplication::getCancellationReasons();

        // Show a row for each application
        foreach ($this->housingApps as $app) {
            $term = Term::toString($app->getTerm());
            $mealPlan = HMS_Util::formatMealOption($app->getMealPlan());
            $phone = HMS_Util::formatCellPhone($app->getCellPhone());

            $type = $app->getPrintableAppType();

            // Clean/dirty and early/late preferences are only fields on the FallApplication
            if ($app instanceof FallApplication && isset($app->room_condition)) {
                $clean = $app->room_condition == 1 ? 'Neat' : 'Cluttered';
            } else {
                $clean = '';
            }

            if ($app instanceof FallApplication && isset($app->preferred_bedtime)) {
                $bedtime = $app->preferred_bedtime == 1 ? 'Early' : 'Late';
            } else {
                $bedtime = '';
            }

            $viewCmd = CommandFactory::getCommand('ShowApplicationView');
            $viewCmd->setAppId($app->getId());

            $view = $viewCmd->getURI();

            $row = array('term' => $term, 'type' => $type, 'meal_plan' => $mealPlan, 'cell_phone' => $phone,
                'clean' => $clean, 'bedtime' => $bedtime, 'view'=>$view);
            if ($app->isCancelled()) {
                $reInstateCmd = CommandFactory::getCommand('ReinstateApplication');
                $reInstateCmd->setAppID($app->getId());
                $reInstateCmd->setBannerId($app->getBannerId());
                $reInstateCmd->setUsername($app->getUsername());
                $row['reinstate'] = $reInstateCmd->getURI();
                $cancelledReason = "({$reasons[$app->getCancelledReason()]})";
                $row['cancelledReason'] = $cancelledReason;
                $row['row_style'] = 'warning';
            } else {
                // Show Cancel Command, if user has permission to cancel apps
                if (Current_User::allow('hms', 'cancel_housing_application')) {
                    $cancelCmd = CommandFactory::getCommand('ShowCancelHousingApplication');
                    $cancelCmd->setHousingApp($app);
                    $cancel = $cancelCmd->getURI();
                    $row['cancel'] = $cancel;
                }
            }


            $app_rows[] = $row;
        }

        $tpl['APPLICATIONS'] = $app_rows;


        return PHPWS_Template::process($tpl, 'hms', 'admin/profileHousingAppList.tpl');
    }

}
