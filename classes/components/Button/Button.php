<?php

namespace components\Button;

use components\Component;
use components\groups\Dimensioning;
use components\groups\Visibility;
use components\IComponent;
use components\Icon;

class Button extends Component implements IComponent
{
    public string $label;// structural
    public Icon $icon;//structural
    public bool $disabled;//structural
    use Visibility;
    use Dimensioning;

    public function __construct($id, $pageId, $name, $type,$path=NULL, $label = null, $icon = null, $disabled = false)
    {
        parent::__construct($id, $pageId, $name, $type,$path);
        if (isset($label)) $this->label = $label;
        if (isset($icon)) $this->icon = $icon;
        if (isset($disabled)) $this->disabled = $disabled;
    }

    /**
     * @throws \Exception
     */
    public function setIcon($icon, $position)
    {
        // todo shorten =>plsits op in update en create!!
        // todo fix bug Pencil vs Trash
        // de fout zit in de create method want bij update is alles correct!
        // todo zorg dat je een default position kan bewaard worden
        if (isset($this->icon)) {
            if (isset($icon)) {
                $icons = \Enums\IconType::cases();
                for ($i = 0; $i < sizeof($icons); $i++) {
                    if ($icons[$i]->name === $icon) {
                        $this->icon->icon = $icons[$i];
                        break;
                    }
                }
            }
            if (isset($position)) {
                $positions = \Enums\IconPositionType::cases();
                for ($i = 0; $i < sizeof($positions); $i++) {
                    if ($positions[$i]->name === $position) {
                        $this->icon->position = $positions[$i];
                        break;
                    }
                }
            }
        } else {
            // create new icon
            if (isset($icon)) {
                $icons = \Enums\IconType::cases();
                for ($i = 0; $i < sizeof($icons); $i++) {
                    if ($icons[$i]->name === $icon) {
                        if (isset($position)) {
                            $positions = \Enums\IconPositionType::cases();
                            for ($j = 0; $j < sizeof($positions); $j++) {
                                if ($positions[$j]->name === $position) {
                                    $this->icon = new Icon($icons[$i], $positions[$j]);
                                    break;
                                }
                            }
                        } else {
                            $this->icon = new Icon($icons[$i]);
                        }
                        break;
                    }
                }
            } else throw new \Exception('Invalid init of Icon object');
        }
        //echo '<pre>'.print_r($this->icon, true).'</pre>';
    }
    // todo code methods
    // todo GUI to fill attributes
    public function getAttributes()
    {
        return ['label', 'icon', 'disabled'];
    }
    function getImportStatement()
    {
        // deze method geeft import statements terug voor de app.module.ts imports
        return ['import {ButtonModule} from "primeng/button";'];
    }
    function getImportsStatement()
    {
        // deze method geeft import statements terug voor de app.module.ts imports
        return "\n".'ButtonModule,';
    }

    // todo
    function getControllerVariables()
    {
        return [];
    }
    function getConstructorInjections()
    {
        return [];
    }
    function getControllerImports()
    {
        return [];
    }

    function getInit($pages)
    {
        return '';
    }

    function getHTML(string $triggers,\Action $action=null)
    {
        if($this->disabled){
            if(isset($this->icon)){
                return '<p-button '.$triggers.' label="'.$this->label.'" icon="'.$this->icon->icon->value.'" iconPos="'.$this->icon->position->value.'" [disabled]="true"></p-button>';
            }
            return '<p-button '.$triggers.' label="'.$this->label.'" [disabled]="true"></p-button>';
        }
        if(isset($this->icon)){
            return '<p-button '.$triggers.' label="'.$this->label.'" icon="'.$this->icon->icon->value.'" iconPos="'.$this->icon->position->value.'"></p-button>';
        }
        return '<p-button '.$triggers.' label="'.$this->label.'"></p-button>';
    }


}
