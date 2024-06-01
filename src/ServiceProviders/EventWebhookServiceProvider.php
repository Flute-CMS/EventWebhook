<?php

namespace Flute\Modules\EventWebhook\src\ServiceProviders;

use Flute\Core\Support\ModuleServiceProvider;
use Flute\Modules\EventWebhook\src\Services\WebhookEventListener;
use Flute\Modules\EventWebhook\src\Discord\Message\DiscordEmbedMessage;
use Flute\Modules\EventWebhook\src\Discord\Webhook\DiscordWebhook;
use Flute\Modules\EventWebhook\src\ServiceProviders\Extensions\AdminExtension;

class EventWebhookServiceProvider extends ModuleServiceProvider
{
    public array $extensions = [
        AdminExtension::class
    ];

    public function boot(\DI\Container $container): void
    {
        $this->loadRoutesFrom('app/Modules/EventWebhook/src/routes.php');

        $this->loadEntities();
        
        $container->get(WebhookEventListener::class)->listen();
    }

    public function register(\DI\Container $container): void
    {
        $this->loadTranslations();
    }
}