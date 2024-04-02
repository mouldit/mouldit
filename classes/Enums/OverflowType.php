<?php

namespace Enums;

enum OverflowType: string
{
    case Auto = 'auto';
    case Scroll = 'scroll';
    case Hidden = 'hidden';
    case Visible = 'visible';
    case HorizontalAuto = 'overflow-auto';
    case HorizontalVisible = 'overflow-visible';
    case HorizontalHidde = 'overflow-hidden';
}
