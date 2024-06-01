<?php

namespace Flute\Modules\EventWebhook\src\ServiceProviders\Extensions;

use Flute\Core\Admin\Builders\AdminSidebarBuilder;

class AdminExtension implements \Flute\Core\Contracts\ModuleExtensionInterface
{
    public function register(): void
    {
        $this->addSidebar();
    }

    private function addSidebar(): void
    {
        AdminSidebarBuilder::add('additional', [
            'title' => 'eventwebhook.admin.header',
            'icon' => 'ph-discord-logo',
            'permission' => 'admin.event_webhook',
            'url' => '/admin/event_webhook/list'
        ]);
    }
}