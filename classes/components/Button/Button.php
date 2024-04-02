<?php

namespace components\Button;

use components\Component;
use components\IComponent;
use components\Icon;
use Enums\IconType;

class Button extends Component implements IComponent
{
    public string $label;
    public Icon $icon;
    public bool $disabled;

    public function __construct($id, $pageId, $name, $type, $label = null, $icon = null, $disabled = false)
    {
        parent::__construct($id, $pageId, $name, $type);
        if (isset($label)) $this->label = $label;
        if (isset($icon)) $this->icon = $icon;
        if (isset($disabled)) $this->disabled = $disabled;
    }

    /**
     * @throws \Exception
     */
    public function setIcon($icon, $position)
    {
        // todo shorten
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
            if (isset($icon)) {
                $icons = \Enums\IconType::cases();
                for ($i = 0; $i < sizeof($icons); $i++) {
                    if ($icons[$i]->name === $icon) {
                        if (isset($position)) {
                            $positions = \Enums\IconPositionType::cases();
                            for ($i = 0; $i < sizeof($positions); $i++) {
                                if ($positions[$i]->name === $position) {
                                    $this->icon = new Icon($icons[$i], $positions[$i]);
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
    }
    // todo code methods
    // todo GUI to fill attributes
    public function getAttributes()
    {
        return ['label', 'icon', 'disabled'];
    }
    function getImportStatement()
    {
        return "\nimport {ButtonModule} from \"primeng/button\";";
    }
    function getComponentImportStatements(int $levelsOfNesting, array $pages)
    {
        return '';
    }
    function getImportsStatement()
    {
        return "\n".'ButtonModule,';
    }
    function getVariables()
    {
        return "";
    }
    function getInit($pages)
    {
        return '';
    }
    function getConstructor()
    {
        return '';
    }
    function getHTML()
    {
        if($this->disabled){
            return '<p-button label="'.$this->label.'" icon="'.$this->icon->icon->value.'" iconPos="'.$this->icon->position->value.'" [disabled]="true"></p-button>';
        }
        return '<p-button label="'.$this->label.'" icon="'.$this->icon->icon->value.'" iconPos="'.$this->icon->position->value.'"></p-button>';
    }
}
