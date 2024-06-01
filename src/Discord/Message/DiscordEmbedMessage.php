<?php

namespace Flute\Modules\EventWebhook\src\Discord\Message;

class DiscordEmbedMessage extends AbstractDiscordMessage
{
    private ?string $title = null;
    private ?string $description = null;
    private ?string $url = null;
    private ?int $color = null;
    private ?\DateTimeInterface $timestamp = null;
    private ?string $footer_icon = null;
    private ?string $footer_text = null;
    private ?string $thumbnail = null;
    private ?string $image = null;
    private ?string $author_name = null;
    private ?string $author_url = null;
    private ?string $author_icon = null;
    private array $fields = [];

    private array $embeds = [];

    public function jsonSerialize(): array
    {
        return [
            'username' => $this->username,
            'content' => $this->content,
            'avatar_url' => $this->avatar,
            'tts' => $this->tts,
            'embeds' => array_map(function ($embed) {
                return [
                    'title' => $embed->getTitle(),
                    'description' => $embed->getDescription(),
                    'timestamp' => $embed->getTimestamp() ? $embed->getTimestamp()->format('Y-m-d\TH:i:sP') : null,
                    'url' => $embed->getUrl(),
                    'color' => $embed->getColor(),
                    'author' => [
                        'name' => $embed->getAuthorName(),
                        'url' => $embed->getAuthorUrl(),
                        'icon_url' => $embed->getAuthorIcon(),
                    ],
                    'image' => [
                        'url' => $embed->getImage(),
                    ],
                    'thumbnail' => [
                        'url' => $embed->getThumbnail(),
                    ],
                    'fields' => $embed->getFields(),
                    'footer' => [
                        'text' => $embed->getFooterText(),
                        'icon_url' => $embed->getFooterIcon(),
                    ],
                ];
            }, $this->embeds),
        ];
    }

    public function addEmbed(self $embed): self
    {
        $this->embeds[] = $embed;
        return $this;
    }

    public function getEmbeds(): array
    {
        return $this->embeds;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getColor(): ?int
    {
        return $this->color;
    }

    public function setColor(int $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getTimestamp(): ?\DateTimeInterface
    {
        return $this->timestamp;
    }

    public function setTimestamp(\DateTimeInterface $timestamp): self
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    public function getFooterIcon(): ?string
    {
        return $this->footer_icon;
    }

    public function setFooterIcon(string $footer_icon): self
    {
        $this->footer_icon = $footer_icon;

        return $this;
    }

    public function getFooterText(): ?string
    {
        return $this->footer_text;
    }

    public function setFooterText(string $footer_text): self
    {
        $this->footer_text = $footer_text;

        return $this;
    }

    public function getThumbnail(): ?string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(string $thumbnail): self
    {
        $this->thumbnail = $thumbnail;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getAuthorName(): ?string
    {
        return $this->author_name;
    }

    public function setAuthorName(string $author_name): self
    {
        $this->author_name = $author_name;

        return $this;
    }

    public function getAuthorUrl(): ?string
    {
        return $this->author_url;
    }

    public function setAuthorUrl(string $author_url): self
    {
        $this->author_url = $author_url;

        return $this;
    }

    public function getAuthorIcon(): ?string
    {
        return $this->author_icon;
    }

    public function setAuthorIcon(string $author_icon): self
    {
        $this->author_icon = $author_icon;

        return $this;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setFields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function addField(string $title, string $value, bool $inLine = false): self
    {
        $this->fields[] = [
            'name' => $title,
            'value' => $value,
            'inline' => $inLine,
        ];

        return $this;
    }

    public function removeField(string $title): bool
    {
        foreach ($this->fields as $key => $field) {
            if ($field['name'] === $title) {
                unset($this->fields[$key]);

                return true;
            }
        }

        return false;
    }

    public function getField(string $title): array
    {
        return $this->findFieldByTitle($title);
    }

    public function setColorWithHexValue(string $hexValue): self
    {
        $hexValue = str_replace('#', '', $hexValue);
        $this->color = hexdec($hexValue);

        return $this;
    }

    protected function findFieldByTitle(string $title): array
    {
        foreach ($this->fields as $field) {
            if ($field['name'] === $title) {
                return $field;
            }
        }

        return [];
    }
}
