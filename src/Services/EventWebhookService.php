<?php

namespace Flute\Modules\EventWebhook\src\Services;

use Flute\Modules\EventWebhook\database\Entities\EventWebhook;
use Nette\Utils\Json;

class EventWebhookService
{
    public function find($id)
    {
        $eventWebhook = rep(EventWebhook::class)
            ->select()
            ->where(['id' => $id])
            ->fetchOne();

        if (!$eventWebhook) {
            throw new \Exception(__('event_webhook.not_found'));
        }

        return $eventWebhook;
    }

    public function store(string $event, string $webhookUrl, string $webhookName, ?string $webhookAvatar, string $content, $embeds)
    {
        $eventWebhook = new EventWebhook();

        $eventWebhook->event = $event;
        $eventWebhook->webhook_url = $webhookUrl;
        $eventWebhook->webhook_name = $webhookName;
        $eventWebhook->webhook_avatar = $webhookAvatar;
        $eventWebhook->content = $content;
        $eventWebhook->embeds = Json::encode($embeds);

        transaction($eventWebhook)->run();
    }

    public function update(int $id, string $event, string $webhookUrl, string $webhookName, ?string $webhookAvatar, string $content, $embeds)
    {
        $eventWebhook = $this->find($id);

        $eventWebhook->event = $event;
        $eventWebhook->webhook_url = $webhookUrl;
        $eventWebhook->webhook_name = $webhookName;
        $eventWebhook->webhook_avatar = $webhookAvatar;
        $eventWebhook->content = $content;
        $eventWebhook->embeds = Json::encode($embeds);

        transaction($eventWebhook)->run();
    }

    public function delete(int $id): void
    {
        $eventWebhook = $this->find($id);

        transaction($eventWebhook, 'delete')->run();
    }
}
