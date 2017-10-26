<?php

namespace Homestead;

class SpringContextApplicationFactory extends ContextApplicationFactory {

    public function populateApplicationSpecificFields()
    {
        $this->app->setApplicationType('spring');
        $this->app->setLifestyleOption($this->context->get('lifestyle_option'));
        $this->app->setPreferredBedtime($this->context->get('preferred_bedtime'));
        $this->app->setRoomCondition($this->context->get('room_condition'));
        $this->app->setSmokingPreference($this->context->get('smoking_preference'));
    }
}
