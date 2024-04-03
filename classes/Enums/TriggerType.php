<?php

namespace Enums;

enum TriggerType
{
    case OnClick;
    case OnComponentReady;
    case OnComponentInit;
    case OnCursorLeave;
    case OnCursorEnter;
}
