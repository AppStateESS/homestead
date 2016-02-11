<?php

namespace hms;

class MajorFactory {

    public static function getMajorsList()
    {
        $url = "https://www.kimonolabs.com/api/cfz7xmva?apikey=5b853442f3f828997cd1a5f0e341e6e1";
        $json = file_get_contents($url);
        $results = json_decode($json, TRUE);

        $majors = array();
        $majors[] = 'Prefer not to say';
        $majors[] = 'Undecided';

        foreach($results['results']['Major List'] as $item)
        {
            if(stristr($item['Major']['text'], 'see ') === FALSE)
            {
                $majors[] = $item['Major']['text'];
            }
        }

        return $majors;
    }
}
