<?php

namespace Homestead;

class FallContextApplicationFactory extends ContextApplicationFactory {

    public function populateApplicationSpecificFields()
    {
        $lifestyleOption	= $this->context->get('lifestyle_option');
        $preferredBedtime	= $this->context->get('preferred_bedtime');
        $roomCondition		= $this->context->get('room_condition');
        $smokingPreference  = $this->context->get('smoking_preference');

        if(!is_numeric($lifestyleOption) || !is_numeric($preferredBedtime) || !is_numeric($roomCondition)){
            //throw new \InvalidArgumentException('Invalid option from context. Please try again.');
        }

        // Load the fall-specific fields
        $this->app->setLifestyleOption($lifestyleOption);
        $this->app->setPreferredBedtime($preferredBedtime);
        $this->app->setRoomCondition($roomCondition);
        $this->app->setSmokingPreference($smokingPreference);

        $rlcInterest = $this->context->get('rlc_interest');
        if(isset($rlcInterest)){
            $this->app->setRlcInterest($this->context->get('rlc_interest'));
        }else{
            $this->app->setRlcInterest(0);
        }

        $this->app->setApplicationType('fall');
    }
}
