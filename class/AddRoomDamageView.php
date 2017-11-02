<?php

namespace Homestead;

/**
 *
 * @author jbooker
 * @deprecated
 */
class AddRoomDamageView extends View {

    private $room;

    public function __construct(Room $room)
    {
        if ($room->getId() == 0) {
            throw new \Exception('Ivalid room object.');
        }

        $this->room = $room;
    }

    public function show()
    {
        $submitCmd = CommandFactory::getCommand('AddRoomDamage');
        $submitCmd->setRoom($this->room);

        $form = new \PHPWS_Form('addDamageForm');


        $submitCmd->initForm($form);

        $damageTypes = DamageTypeFactory::getDamageTypeAssoc();

        $categories = array ();

        foreach ($damageTypes as $id => $attribs) {
            $categories[$attribs['category']][$id] = $attribs['description'];
        }

        /*
        $types = array();

        foreach ($damageTypes as $key => $t) {
            $types[$key] = $t['category'] . ' - ' . $t['description'];
        }

        $form->addDropBox('damage_type', $types);
        */

        $damageDrop = '<select data-placeholder="Select Damage Type" class="chzn-select" id="phpws-form-damage-type" name="damage_type" placeholder="Select Damage Type"><option value=""></option>';

        foreach ($categories as $categoryName => $options) {
            $damageDrop .= "<optgroup label=\"$categoryName\">\n";

            foreach ($options as $value => $text) {
                $damageDrop .= "<option value=\"$value\">$text</option>\n";
            }

            $damageDrop .= '<optgroup>\n';
        }

        $damageDrop .= "</select>";

        $tags = array();

        $tags['DAMAGE_TYPE'] = $damageDrop;

        $form->addDropBox('side', array (
                'Both' => 'Both',
                'Left' => 'Left',
                'Right' => 'Right'
        ));

        $form->addTextArea('note');

        $form->mergeTemplate($tags);
        $tags = $form->getTemplate();

        // var_dump($tags);
        // exit;

        return \PHPWS_Template::process($tags, 'hms', 'admin/addRoomDamage.tpl');
    }
}
