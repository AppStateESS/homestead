<?php

namespace Homestead;

use \Homestead\Exception\HMSException;

class PreferenceValues{

    private $gender;
    private $lifestyle;
    private $condition;
    private $bedtime;

    public function __construct()
    {
        $this->gender    = 0;
        $this->lifestyle = 1;
        $this->condition = 1;
        $this->bedtime   = 1;
        $this->smoking   = 1;
    }

    public function increment()
    {

        if($this->bedtime < 2) {
            $this->bedtime = 2;
        } else {
            $this->bedtime = 1;

            if($this->condition < 2) {
                $this->condition = 2;
            } else {
                $this->condition = 1;

                if($this->lifestyle < 2) {
                    $this->lifestyle = 2;
                } else {
                    $this->lifestyle = 1;

                    if($this->smoking <2)
                    {
                      $this->smoking = 2;
                    }
                    else{
                      $this->smoking = 1;

                      if($this->gender < 1) {
                        $this->gender = 1;
                      } else {
                        throw new HMSException("Can Not Increment.");
                      }
                    }
                }
            }
        }
    }

    public static function compatible($a, $b)
    {
        return $a instanceof HousingApplication && $b instanceof HousingApplication &&
        $a->gender == $b->gender;
    }

    public function accept(HousingApplication $app)
    {
        return
        $app->gender == $this->gender &&
        $app->lifestyle_option == $this->lifestyle &&
        $app->room_condition == $this->condition &&
        $app->preferred_bedtime == $this->bedtime &&
        $app->smoking_preference == $this->smoking;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function getLifestyle()
    {
        return $this->lifestyle;
    }

    public function getCondition()
    {
        return $this->condition;
    }

    public function getBedtime()
    {
        return $this->bedtime;
    }

    public function getSmoking()
    {
      return $this->smoking;
    }
}
