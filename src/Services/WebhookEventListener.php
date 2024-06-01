<?php

namespace Flute\Modules\EventWebhook\src\Services;

use Flute\Modules\EventWebhook\database\Entities\EventWebhook;
use Flute\Modules\EventWebhook\src\Discord\Message\DiscordEmbedMessage;
use Flute\Modules\EventWebhook\src\Discord\Webhook\DiscordWebhook;
use Nette\Utils\Json;

class WebhookEventListener
{
    public function listen()
    {
        $events = rep(EventWebhook::class)->findAll();

        foreach ($events as $event) {
            events()->addDeferredListener($event->event, function ($eventInstance) use ($event) {
                $webhookMessage = $this->createWebhookMessage($event, $eventInstance);
                $this->sendWebhook($event->webhook_url, $webhookMessage);
            });
        }
    }

    private function createWebhookMessage(EventWebhook $eventWebhook, $eventInstance): DiscordEmbedMessage
    {
        $message = new DiscordEmbedMessage();
        $message->setUsername($eventWebhook->webhook_name)
            ->setContent($this->replaceContent($eventWebhook->content, $eventInstance))
            ->setAvatar($eventWebhook->webhook_avatar);

        $embedsData = Json::decode($eventWebhook->embeds, Json::FORCE_ARRAY);

        foreach ($embedsData as $embedData) {
            $embed = new DiscordEmbedMessage();
            $embed->setTitle($this->replaceContent($embedData['body']['title'] ?? '', $eventInstance))
                ->setDescription($this->replaceContent($embedData['body']['description'] ?? '', $eventInstance))
                ->setUrl($embedData['body']['url'] ?? '')
                ->setColorWithHexValue($embedData['body']['color'] ?? 'ffffff');

            if (isset($embedData['author'])) {
                $embed->setAuthorName($this->replaceContent($embedData['author']['name'] ?? '', $eventInstance))
                    ->setAuthorUrl($embedData['author']['url'] ?? '')
                    ->setAuthorIcon($embedData['author']['icon'] ?? '');
            }

            if (isset($embedData['footer'])) {
                $embed->setFooterText($this->replaceContent($embedData['footer']['text'] ?? '', $eventInstance))
                    ->setFooterIcon($embedData['footer']['icon'] ?? '');

                if (isset($embedData['footer']['time'])) {
                    $embed->setTimestamp(new \DateTime($embedData['footer']['time'] ?? 'now'));
                }
            }

            if (isset($embedData['thumbnail'])) {
                $embed->setThumbnail($embedData['thumbnail']['url'] ?? '');
            }

            if (isset($embedData['image'])) {
                $embed->setImage($embedData['image']['url'] ?? '');
            }

            if (isset($embedData['fields']) && is_array($embedData['fields'])) {
                foreach ($embedData['fields'] as $field) {
                    $embed->addField(
                        $this->replaceContent($field['name'] ?? '', $eventInstance),
                        $this->replaceContent($field['value'] ?? '', $eventInstance),
                        $field['inline'] ?? false
                    );
                }
            }

            $message->addEmbed($embed);
        }

        return $message;
    }

    private function replaceContent(string $content, $eventInstance): string
    {
        return preg_replace_callback('/\{(.*?)\}/', function ($matches) use ($eventInstance) {
            $parts = explode('.', $matches[1]);
            if (count($parts) == 2 && method_exists($eventInstance, $parts[0])) {
                return $eventInstance->{$parts[0]}()->{$parts[1]};
            } elseif (count($parts) == 1 && method_exists($eventInstance, $parts[0])) {
                return $eventInstance->{$parts[0]}();
            } elseif (property_exists($eventInstance, $matches[1])) {
                return $eventInstance->{$matches[1]};
            } else {
                return $matches[0];
            }
        }, $content);
    }

    private function sendWebhook(string $webhookUrl, DiscordEmbedMessage $message)
    {
        try {
        $discordWebhook = new DiscordWebhook($webhookUrl);
        $discordWebhook->send($message);
        } catch (\Exception $e) {
            logs()->error($e);
        }
    }
}
