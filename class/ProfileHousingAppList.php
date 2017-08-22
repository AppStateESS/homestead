<?php

namespace Homestead;

/*use \hms\DocusignClientFactory;
use \hms\HousingApplication;*/

/**
 * ProfileHousingAppList - View to show the list of houing apps on the Student Profile.
 *
 * @author jbooker
 */
class ProfileHousingAppList extends View
{
    private $student;
    private $housingApps;
    private $docusignClient;

    public function __construct(Student $student, Array $housingApps)
    {
        $this->student = $student;
        $this->housingApps = $housingApps;

        $this->docusignClient = DocusignClientFactory::getClient();
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

                $contract = ContractFactory::getContractByStudentTerm($this->student, $app->getTerm());
                if($contract !== false){
                    $envelope = \Docusign\EnvelopeFactory::getEnvelopeById($this->docusignClient, $contract->getEnvelopeId());
                    $row['contract'] = $envelope->getEnvelopeViewURI($this->docusignClient);
                } else {
                    $row['contract'] = 'No Contract';
                }

            }


            $app_rows[] = $row;
        }

        $tpl['APPLICATIONS'] = $app_rows;


        return PHPWS_Template::process($tpl, 'hms', 'admin/profileHousingAppList.tpl');
    }

}
