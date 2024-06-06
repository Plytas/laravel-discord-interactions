<?php

namespace Plytas\Discord\Enums;

enum ComponentType: int
{
    case ActionRow = 1;
    case Button = 2;
    case StringSelect = 3;
    case TextInput = 4;
    case UserSelect = 5;
    case RoleSelect = 6;
    case MentionableSelect = 7;
    case ChannelSelect = 8;
}
