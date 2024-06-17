<?php

namespace Plytas\Discord\Enums;

enum ChannelType: int
{
    case GuildText = 0;
    case DM = 1;
    case GuildVoice = 2;
    case GroupDM = 3;
    case GuildCategory = 4;
    case GuildNews = 5;
    case AnnouncementThread = 10;
    case PublicThread = 11;
    case PrivateThread = 12;
    case GuildStageVoice = 13;
    case GuildDirectory = 14;
    case GuildForum = 15;
    case GuildMedia = 16;
}
