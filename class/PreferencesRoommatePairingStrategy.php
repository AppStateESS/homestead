<?php

namespace Homestead;

use \Homestead\Exception\HMSException;

class PreferencesRoommatePairingStrategy extends RoommatePairingStrategy {

    private $values;

    public function __construct($term)
    {
        parent::__construct($term);
        $this->values = new PreferenceValues();
    }

    public function doPairing(&$applications, &$pairs)
    {
        $keys = $this->sort($applications);

        while(count($applications) > 1) {
            $a = array_shift($keys);
            $b = array_shift($keys);

            if(!$a || !$b || !PreferenceValues::compatible($applications[$a], $applications[$b])) {
                //echo "Poor $a doesn't get a roommate.\n";
                if($b) {
                    array_unshift($b);
                } else break;   // None left.
            }

            //echo "Pairing:\n" .
            //    $this->formatApp($applications[$a]) . "\n" .
            //    $this->formatApp($applications[$b]) . "\n\n";

            $newPair = $this->createPairing($applications[$a], $applications[$b]);

            if(!is_null($newPair)){
                $pairs[] = $newPair;
            }

            unset($applications[$a]);
            unset($applications[$b]);
        }
    }

    private function sort(&$apps)
    {
        $v = $this->values;
        $k = array();

        try {
            while(1) {
                foreach($apps as $username => $app) {
                    if($v->accept($app)) {
                        $k[] = $username;
                        echo $this->formatApp($app)."\n";
                    }
                }
                $v->increment();
            }
        } catch (HMSException $e) {}

        return $k;
    }

    private function formatApp(HousingApplication $app)
    {
        return
        $app->username . "\t" .
        (strlen($app->username) < 8 ? "\t" : '') .
        $app->gender . "\t" .
        $app->lifestyle_option . "\t" .
        $app->room_condition . "\t" .
        $app->preferred_bedtime . "\t" .
        $app->smoking_preference;
    }
}

class PreferenceValues
{
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
