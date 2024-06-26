<?php

namespace Flute\Modules\EventWebhook\src\Services;

use Flute\Core\Support\ContentParser;
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
            events()->addDeferredListener($event->event, [$this, 'handleEvent']);
        }
    }

    public static function handleEvent($eventInstance)
    {
        if ($eventInstance::NAME) {
            $events = rep(EventWebhook::class)->select()->where('event', $eventInstance::NAME)->fetchAll();

            foreach ($events as $event) {
                $webhookMessage = self::createWebhookMessage($event, $eventInstance);
                self::sendWebhook($event->webhook_url, $webhookMessage);
            }
        }
    }

    private static function createWebhookMessage(EventWebhook $eventWebhook, $eventInstance): DiscordEmbedMessage
    {
        $user = method_exists($eventInstance, 'getUser') ? $eventInstance->getUser() : user()->getCurrentUser();

        $message = new DiscordEmbedMessage();
        $message->setUsername($eventWebhook->webhook_name)
            ->setContent(ContentParser::replaceContent($eventWebhook->content, $eventInstance, $user))
            ->setAvatar($eventWebhook->webhook_avatar);

        $embedsData = Json::decode($eventWebhook->embeds, Json::FORCE_ARRAY);

        foreach ($embedsData as $embedData) {
            $embed = new DiscordEmbedMessage();

            $embed->setTitle(ContentParser::replaceContent($embedData['body']['title'] ?? '', $eventInstance, $user))
                ->setDescription(ContentParser::replaceContent($embedData['body']['description'] ?? '', $eventInstance, $user))
                ->setUrl($embedData['body']['url'] ?? '')
                ->setColorWithHexValue($embedData['body']['color'] ?? 'ffffff');

            if (isset($embedData['author'])) {
                $embed->setAuthorName(ContentParser::replaceContent($embedData['author']['name'] ?? '', $eventInstance, $user))
                    ->setAuthorUrl($embedData['author']['url'] ?? '')
                    ->setAuthorIcon($embedData['author']['icon'] ?? '');
            }

            if (isset($embedData['footer'])) {
                $embed->setFooterText(ContentParser::replaceContent($embedData['footer']['text'] ?? '', $eventInstance, $user))
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
                        ContentParser::replaceContent($field['name'] ?? '', $eventInstance, $user),
                        ContentParser::replaceContent($field['value'] ?? '', $eventInstance, $user),
                        $field['inline'] ?? false
                    );
                }
            }

            $message->addEmbed($embed);
        }

        return $message;
    }

    private static function sendWebhook(string $webhookUrl, DiscordEmbedMessage $message)
    {
        try {
            $discordWebhook = new DiscordWebhook($webhookUrl);
            $discordWebhook->send($message);
        } catch (\Exception $e) {
            logs()->error($e);
        }
    }
}
