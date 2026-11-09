<?php

namespace App\Enums;

use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

enum SpeakerQualification: string implements HasDescription, HasLabel
{
    case BusinessLeader = 'business_leader';
    case Charisma = 'charisma';
    case FirstTime = 'first_time';
    case HometownHero = 'hometown_hero';
    case Humanitarian = 'humanitarian';
    case OpenSource = 'open_source';
    case PublishedAuthor = 'published_author';
    case YoutubeInfluencer = 'youtube_influencer';

    public function getLabel(): string
    {
        return match ($this) {
            self::BusinessLeader => 'Business Leader',
            self::Charisma => 'Charismatic Speaker',
            self::FirstTime => 'First Time Speaker',
            self::HometownHero => 'Hometown Hero',
            self::Humanitarian => 'Works in Humanitarian Field',
            self::OpenSource => 'Open Source Creator / Maintainer',
            self::PublishedAuthor => 'Published Author',
            self::YoutubeInfluencer => 'Large YouTube Following',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::BusinessLeader => 'Runs or helps lead a company.',
            self::Charisma => 'Known for engaging, high-energy talks.',
            self::FirstTime => 'Has never spoken at a conference before.',
            self::HometownHero => 'Based in the conference city.',
            self::Humanitarian => 'Works for a non-profit or charitable cause.',
            self::OpenSource => 'Creates or maintains open source projects.',
            self::PublishedAuthor => 'Has written a book or published course.',
            self::YoutubeInfluencer => 'Has a sizeable YouTube audience.',
        };
    }
}
