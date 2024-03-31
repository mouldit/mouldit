<?php

namespace components;

use Enums\IconPositionType;
use Enums\IconType;

class Icon
{
    public IconType $icon;
    public IconPositionType $position;
    public function __construct(IconType $icon,IconPositionType $position=IconPositionType::Left)
    {
        $this->icon=$icon;
        $this->position=$position;
    }
}