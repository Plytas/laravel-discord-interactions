<?php

namespace Plytas\Discord\Data;

use Illuminate\Support\Collection;
use Plytas\Discord\Contracts\DiscordComponent;
use Spatie\LaravelData\Data;

class DiscordMessage extends Data
{
    public function __construct(
        public ?string $content = null,
        public int $flags = 0,
        /** @var Collection<int, DiscordMessageEmbed> */
        public Collection $embeds = new Collection(),
        /** @var Collection<int, DiscordComponent> */
        public Collection $components = new Collection(),
        /** @var array<int, mixed> */
        public ?array $attachments = null,
        /** @var Collection<int, DiscordMessageFile> */
        private Collection $files = new Collection(),
    ) {}

    public static function new(): self
    {
        return new self();
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function ephemeral(): self
    {
        $this->flags |= 1 << 6;

        return $this;
    }

    public function addEmbed(?DiscordMessageEmbed $embed): self
    {
        if (! $embed instanceof DiscordMessageEmbed) {
            return $this;
        }

        $this->embeds->push($embed);

        return $this;
    }

    public function addComponent(DiscordComponent $component): self
    {
        $this->components->push($component);

        return $this;
    }

    public function removeAttachments(): self
    {
        $this->attachments = [];

        return $this;
    }

    public function addFile(DiscordMessageFile $file): self
    {
        $this->files->push($file);

        return $this;
    }

    /**
     * @return Collection<int, DiscordMessageFile>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function isMultipart(): bool
    {
        return $this->files->isNotEmpty();
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        if ($this->attachments === null) {
            unset($array['attachments']);
        }

        return $array;
    }
}
