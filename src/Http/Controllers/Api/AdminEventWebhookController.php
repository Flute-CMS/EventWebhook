<?php

namespace Flute\Modules\EventWebhook\src\Http\Controllers\Api;

use Flute\Core\Admin\Http\Middlewares\HasPermissionMiddleware;
use Flute\Core\Support\AbstractController;
use Flute\Core\Support\FluteRequest;
use Flute\Modules\EventWebhook\src\Services\EventWebhookService;
use Symfony\Component\HttpFoundation\Response;

class AdminEventWebhookController extends AbstractController
{
    protected $eventWebhookService;

    public function __construct(EventWebhookService $eventWebhookService)
    {
        $this->eventWebhookService = $eventWebhookService;

        HasPermissionMiddleware::permission(['admin', 'admin.event_webhook']);
    }

    public function store(FluteRequest $request): Response
    {
        try {
            $this->eventWebhookService->store(
                $request->event,
                $request->webhook_url,
                $request->webhook_name,
                $request->webhook_avatar,
                $request->content,
                $request->input('embeds', [])
            );

            return $this->success();
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function delete(FluteRequest $request, $id): Response
    {
        try {
            $this->eventWebhookService->delete((int) $id);

            return $this->success();
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }

    public function update(FluteRequest $request, $id): Response
    {
        try {
            $this->eventWebhookService->update(
                (int) $id,
                $request->event,
                $request->webhook_url,
                $request->webhook_name,
                $request->webhook_avatar,
                $request->content,
                $request->input('embeds', [])
            );

            return $this->success();
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
