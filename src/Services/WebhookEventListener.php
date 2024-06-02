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
        $message = new DiscordEmbedMessage();
        $message->setUsername($eventWebhook->webhook_name)
            ->setContent(self::replaceContent($eventWebhook->content, $eventInstance))
            ->setAvatar($eventWebhook->webhook_avatar);

        $embedsData = Json::decode($eventWebhook->embeds, Json::FORCE_ARRAY);

        foreach ($embedsData as $embedData) {
            $embed = new DiscordEmbedMessage();
            $embed->setTitle(self::replaceContent($embedData['body']['title'] ?? '', $eventInstance))
                ->setDescription(self::replaceContent($embedData['body']['description'] ?? '', $eventInstance))
                ->setUrl($embedData['body']['url'] ?? '')
                ->setColorWithHexValue($embedData['body']['color'] ?? 'ffffff');

            if (isset($embedData['author'])) {
                $embed->setAuthorName(self::replaceContent($embedData['author']['name'] ?? '', $eventInstance))
                    ->setAuthorUrl($embedData['author']['url'] ?? '')
                    ->setAuthorIcon($embedData['author']['icon'] ?? '');
            }

            if (isset($embedData['footer'])) {
                $embed->setFooterText(self::replaceContent($embedData['footer']['text'] ?? '', $eventInstance))
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
                        self::replaceContent($field['name'] ?? '', $eventInstance),
                        self::replaceContent($field['value'] ?? '', $eventInstance),
                        $field['inline'] ?? false
                    );
                }
            }

            $message->addEmbed($embed);
        }

        return $message;
    }

    private static function replaceContent(string $content, $eventInstance): string
    {
        $content = self::replaceUserContent($content);

        return preg_replace_callback('/\{(.*?)\}/', function ($matches) use ($eventInstance) {
            return self::evaluateExpression($matches[1], $eventInstance);
        }, $content);
    }

    private static function evaluateExpression($expression, $eventInstance)
    {
        if (preg_match('/^(\w+)\((.*?)\)$/', $expression, $matches)) {
            $func = $matches[1];
            $args = self::parseArguments($matches[2]);
            if (function_exists($func)) {
                $result = call_user_func_array($func, $args);
                return is_object($result) ? self::getNestedProperty($result, array_slice(explode('.', $expression), 1)) : $result;
            }
        }

        $parts = explode('.', $expression);
        return self::getNestedProperty($eventInstance, $parts);
    }

    private static function getNestedProperty($object, array $parts)
    {
        $current = $object;

        foreach ($parts as $part) {
            if (preg_match('/(\w+)\((.*?)\)$/', $part, $matches)) {
                $func = $matches[1];
                $args = self::parseArguments($matches[2]);
                if (is_object($current) && method_exists($current, $func)) {
                    $current = call_user_func_array([$current, $func], $args);
                } elseif (function_exists($func)) {
                    $current = call_user_func_array($func, $args);
                } else {
                    return '{' . implode('.', $parts) . '}';
                }
            } elseif (is_object($current)) {
                if (method_exists($current, $part)) {
                    $current = $current->{$part}();
                } elseif (property_exists($current, $part)) {
                    $current = $current->{$part};
                } else {
                    return '{' . implode('.', $parts) . '}';
                }
            } else {
                return '{' . implode('.', $parts) . '}';
            }
        }

        return $current;
    }

    private static function parseArguments($argsString)
    {
        $args = [];
        if (!empty($argsString)) {
            $parts = explode(',', $argsString);
            foreach ($parts as $part) {
                $part = trim($part, " \t\n\r\0\x0B'\"");
                $args[] = $part;
            }
        }
        return $args;
    }

    private static function replaceUserContent(string $content)
    {
        return str_replace(['{name}', '{login}', '{email}', '{balance}'], [
            user()->getCurrentUser()->name,
            user()->getCurrentUser()->login,
            user()->getCurrentUser()->email,
            user()->getCurrentUser()->balance
        ], $content);
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
