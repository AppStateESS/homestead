<?php

namespace hms;

class MajorFactory {

    public static function getMajorsList()
    {
        $url = PHPWS_SOURCE_DIR . "/mod/hms/data/majors.json";
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
