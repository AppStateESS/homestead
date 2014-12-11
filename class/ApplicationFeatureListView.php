<?php

PHPWS_Core::initModClass('hms', 'View.php');

class ApplicationFeatureListView extends homestead\View
{
    public static $dir = 'applicationFeature';
    private $features;
    private $term;

    public function __construct($term)
    {
        $this->term = $term;

        PHPWS_Core::initModClass('hms', 'ApplicationFeature.php');
        $this->features = ApplicationFeature::getFeatures();
    }

    public function show()
    {
        $content = array();
        $termFeatures = ApplicationFeature::getAllForTerm(Term::getSelectedTerm());

        PHPWS_Core::initModClass('hms', 'ApplicationFeatureSettingsView.php');
        foreach($this->features as $feature) {
            $tpl = array();
            $tpl['DESCRIPTION'] = $feature->getDescription();

            $class = $feature->getName();
            if(!isset($termFeatures[$class])) {
                $f = new $class();
                $f->setTerm($this->term);
                $termFeatures[$class] = $f;
            }

            $view = new ApplicationFeatureSettingsView($termFeatures[$class]);
            $content[] = $view->show();
        }

        // TODO.. put the HTML in a template
        return '<p>Note: both "Start Date" and "End Date" imply 12:00 AM on those dates.  Effectively, this means that the feature will be available all day on the selected "Start Date", but will not be available at all on the "End Date".</p>'
                .implode('', $content);
    }
}

?>
