<?php

namespace Homestead;

class ApplicationFeatureListView extends View{

    public static $dir = 'ApplicationFeature';
    private $features;
    private $term;

    public function __construct($term)
    {
        $this->term = $term;

        $this->features = ApplicationFeature::getFeatures();
    }

    public function show()
    {
        $tpl = array();
        $termFeatures = ApplicationFeature::getAllForTerm(Term::getSelectedTerm());

        foreach($this->features as $feature) {
            //$featureTpl = array();
            //$featureTpl['DESCRIPTION'] = $feature->getDescription();

            $class = $feature->getName();
            if(!isset($termFeatures[$class])) {
                $f = new $class();
                $f->setTerm($this->term);
                $termFeatures[$class] = $f;
            }

            $view = new ApplicationFeatureSettingsView($termFeatures[$class]);
            $tpl['features'][] = array('feature' => $view->show());
        }

        return \PHPWS_Template::process($tpl, 'hms', 'admin/ApplicationFeaturesList.tpl');
    }
}
