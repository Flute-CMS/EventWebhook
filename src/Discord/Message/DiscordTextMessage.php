<?php

namespace Flute\Modules\EventWebhook\src\Discord\Message;

class DiscordTextMessage extends AbstractDiscordMessage
{
    public function jsonSerialize(): array
    {
        return [
            'content' => $this->content,
            'avatar_url' => $this->avatar,
            'username' => $this->username,
            'tts' => $this->tts,
        ];
    }
}
