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
