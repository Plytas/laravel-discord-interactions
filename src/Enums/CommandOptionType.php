<?php

namespace Plytas\Discord\Enums;

enum CommandOptionType: int
{
    case SUB_COMMAND = 1;
    case SUB_COMMAND_GROUP = 2;
    case STRING = 3;
    case INTEGER = 4;
    case BOOLEAN = 5;
    case USER = 6;
    case CHANNEL = 7;
    case ROLE = 8;
    case MENTIONABLE = 9;
    case NUMBER = 10;
    case ATTACHMENT = 11;

    public function isSubcommandOrGroup(): bool
    {
        return match ($this) {
            CommandOptionType::SUB_COMMAND,
            CommandOptionType::SUB_COMMAND_GROUP => true,
            default => false,
        };
    }
}
