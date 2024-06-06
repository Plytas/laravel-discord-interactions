<?php

namespace Plytas\Discord\Enums;

enum CommandType: int
{
    case ChatInput = 1;
    case User = 2;
    case Message = 3;
}
