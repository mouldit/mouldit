<?php

namespace Enums;

enum TriggerType:string
{
    case OnClick='(click)';
    case OnComponentReady='OnComponentReady';
    case OnComponentInit='OnComponentInit';
    case OnCursorLeave='OnCursorLeave';
    case OnCursorEnter='OnCursorEnter';
}
