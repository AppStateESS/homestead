<?php

namespace Homestead\Command;

 

class SaveApplicationFeatureCommand extends Command {

    private $featureId;
    private $term;
    private $name;

    public function getRequestVars()
    {
        $vars = array('action'=>'SaveApplicationFeature');

        if(isset($this->featureId))
        {
            $vars['featureId'] = $this->featureId;
        }

        if(isset($this->term)) {
            $vars['term'] = $this->term;
        }

        if(isset($this->name)) {
            $vars['name'] = $this->name;
        }

        return $vars;
    }

    public function setFeatureId($id) {
        $this->featureId = $id;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function setTerm($term) {
        $this->term = $term;
    }

    public function execute(CommandContext $context)
    {
        if(!UserStatus::isAdmin() || !\Current_User::allow('hms', 'deadlines')) {
            PHPWS_Core::initModClass('hms', 'exception/PermissionException.php');
            throw new PermissionException('You do not have permission to edit deadlines.');
        }


        PHPWS_Core::initModClass('hms', 'exception/MissingDataException.php');

        if(!isset($this->featureId)) {
            $this->featureId = $context->get('featureId');
        }
        $featureId = $this->featureId;

        if(!isset($this->term)) {
            $this->term = $context->get('term');
        }
        $term = $this->term;

        if(!isset($this->name)) {
            $this->name = $context->get('name');
        }
        $name = $this->name;



        PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');
        if(!is_null($featureId)) {
            $feature = ApplicationFeature::getInstanceById($featureId);
        } else if(!is_null($name) && !is_null($term)) {
            $feature = ApplicationFeature::getInstanceByName($name);
            $feature->setTerm($term);
        } else {
            throw new \InvalidArgumentException('You must either provide a featureId, or a name and a term.');
        }

        // Checkboxes are weird.
        $enabled = !is_null($context->get('enabled'));

        $feature->setEnabled($enabled);
        if($enabled) {
            $startDate = strtotime($context->get('start_date'));
            $editDate   = strtotime($context->get('edit_date'));
            $endDate   = strtotime($context->get('end_date'));

            if($startDate && $endDate) {
                if($startDate >= $endDate){
                    $e = new MissingDataException ('Start date must be before the end date.', array('Start date', 'End date'));
                    echo $e->getJSON();
                    HMS::quit();
                }
                if($editDate && ($editDate <= $startDate || $editDate >= $endDate)) {
                    $e = new MissingDataException('Edit date must be between the start and end dates.', array('Edit date'));
                    echo $e->getJSON();
                    HMS::quit();
                }
            }

            if(!is_null($startDate)) {
                $feature->setStartDate($startDate);
            }

            $registration = $feature->getRegistration();

            if($registration->requiresEditDate()) {
                $feature->setEditDate($editDate + 86399); // Add 23h23m23s so that the end date is actuall 11:59:59pm on the selected day
            } else {
                $feature->setEditDate(0);
            }

            if($registration->requiresEndDate()) {
                $feature->setEndDate($endDate + 86399); // Add 23h23m23s so that the end date is actuall 11:59:59pm on the selected day
            } else {
                $feature->setEndDate(0);

            }
        }

        try {
            $feature->save();
        } catch(MissingDataException $e) {
            echo json_encode($e);
            HMS::quit();
        }

        echo json_encode($feature);
        HMS::quit();
    }
}
