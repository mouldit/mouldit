<?php

namespace components\groups;

use Enums\DisplayType;
use Enums\OverflowType;

trait Dimensioning
{
    public Dimension $width;
    public Dimension $height;
    public mixed $size;
    public bool $stretch;
    public DisplayType $display;
    public OverflowType $overflow;

}