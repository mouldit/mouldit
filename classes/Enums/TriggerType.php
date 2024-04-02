<?php

namespace Enums;

enum TriggerType
{
    case OnClick;
    case OnComponentReady;
    case OnComponentInit;
    case OnHide;
    case OnCursorLeave;
    case OnCursorEnter;
    case OnClose;
}
