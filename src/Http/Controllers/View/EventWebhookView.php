<?php

namespace Flute\Modules\EventWebhook\src\Http\Controllers\View;

use Flute\Core\Admin\Http\Controllers\Views\NotificationsView;
use Flute\Core\Admin\Http\Middlewares\HasPermissionMiddleware;
use Flute\Core\Admin\Services\PageGenerator\AdminTablePage;
use Flute\Core\Support\AbstractController;
use Flute\Core\Support\FluteRequest;
use Flute\Modules\EventWebhook\database\Entities\EventWebhook;
use Flute\Modules\EventWebhook\src\Services\EventWebhookService;

class EventWebhookView extends AbstractController
{
    public function __construct()
    {
        HasPermissionMiddleware::permission(['admin', 'admin.event_webhook']);
    }

    public function list(FluteRequest $request)
    {
        $table = table();

        $table->setPhrases([
            'event' => __('eventwebhook.event'),
            'webhook_name' => __('eventwebhook.admin.webhook_name'),
        ]);

        $table->fromEntity(rep(EventWebhook::class)->findAll(), ['webhook_url', 'webhook_avatar', 'content', 'embeds'])->withActions('event_webhook');

        $pageGenerator = new AdminTablePage();
        $pageContent = $pageGenerator
            ->setTitle('eventwebhook.admin.header')
            ->setHeader('eventwebhook.admin.header')
            ->setDescription('eventwebhook.admin.description')
            ->setContent($table->render())
            ->setWithAddBtn(true)
            ->setBtnAddPath('/admin/event_webhook/add')
            ->generatePage();

        return $pageContent;
    }

    public function add(FluteRequest $request)
    {
        return view(mm('EventWebhook', 'Resources/views/add'), [
            "events" => NotificationsView::$events
        ]);
    }

    public function edit(FluteRequest $request, $id, EventWebhookService $eventWebhookService)
    {
        $webhook = $eventWebhookService->find((int) $id);

        return view(mm('EventWebhook', 'Resources/views/edit'), [
            "events" => NotificationsView::$events,
            "webhook" => $webhook
        ]);
    }
}