<?php

namespace Homestead\Command;

use \Homestead\AssetResolver;

class ShowSuggestedRoommatesCommand extends Command {

    private $bannerId;
    private $term;

    public function getRequestVars(){
        return array(
            'action' => 'ShowSuggestedRoommates',
            'term' => $this->term,
            'bannerId' => $this->bannerId
        );
    }

    public function setBannerId($banner){
        $this->bannerId = $banner;
    }

    public function setTerm($term){
        $this->term = $term;
    }

    public function execute(CommandContext $context){
        $tags = array();

        $tags['vendor_bundle'] = AssetResolver::resolveJsPath('assets.json', 'vendor');
        $tags['entry_bundle'] = AssetResolver::resolveJsPath('assets.json', 'suggestedRoommateList');

        $tags['term'] = $context->get('term');
        $tags['bannerId'] = $context->get('bannerId');

        $context->setContent(\PHPWS_Template::process($tags, 'hms', 'admin/suggestedRoommates.tpl'));
    }
}
