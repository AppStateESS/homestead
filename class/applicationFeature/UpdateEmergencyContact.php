<?php

namespace Homestead\applicationFeature;

use \Homestead\ApplicationFeatureRegistration;

class UpdateEmergencyContactRegistration extends ApplicationFeatureRegistration {
    public function __construct()
    {
        $this->name = 'UpdateEmergencyContact';
        $this->description = 'Update Emergency & Missing Persons Contact';
        $this->startDateRequired = true;
        $this->endDateRequired = true;
        $this->priority = 9;
    }

    public function showForStudent(Student $student, $term)
    {
        return true;
    }
}

class UpdateEmergencyContact extends ApplicationFeature {
    public function getMenuBlockView(Student $student)
    {
        PHPWS_Core::initModClass('hms', 'HousingApplicationFactory.php');
        PHPWS_Core::initModClass('hms', 'UpdateEmergencyContactMenuBlockView.php');

        $application = HousingApplicationFactory::getAppByStudent($student, $this->term);

        return new UpdateEmergencyContactMenuBlockView($student, $this->getStartDate(), $this->getEndDate(), $application);
    }
}
