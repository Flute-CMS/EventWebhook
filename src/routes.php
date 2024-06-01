<?php

use Flute\Core\Admin\Http\Middlewares\HasPermissionMiddleware;
use Flute\Core\Router\RouteGroup;
use Flute\Modules\EventWebhook\src\Http\Controllers\Api\AdminEventWebhookController;
use Flute\Modules\EventWebhook\src\Http\Controllers\View\EventWebhookView;

$router->group(function (RouteGroup $routeGroup) {
    $routeGroup->middleware(HasPermissionMiddleware::class);

    $routeGroup->group(function (RouteGroup $routeGroup) {
        $routeGroup->group(function (RouteGroup $adminRouteGroup) {
            $adminRouteGroup->get('list', [EventWebhookView::class, 'list']);
            $adminRouteGroup->get('add', [EventWebhookView::class, 'add']);
            $adminRouteGroup->get('edit/{id}', [EventWebhookView::class, 'edit']);
        }, 'event_webhook/');

        $routeGroup->group(function (RouteGroup $adminRouteGroup) {
            $adminRouteGroup->post('add', [AdminEventWebhookController::class, 'store']);
            $adminRouteGroup->delete('{id}', [AdminEventWebhookController::class, 'delete']);
            $adminRouteGroup->put('{id}', [AdminEventWebhookController::class, 'update']);
        }, 'api/event_webhook/');
    }, 'admin/');
});