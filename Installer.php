<?php

namespace Flute\Modules\EventWebhook;

use Flute\Core\Database\Entities\Permission;

class Installer extends \Flute\Core\Support\AbstractModuleInstaller
{
    public function install(\Flute\Core\Modules\ModuleInformation &$module): bool
    {
        $permission = rep(Permission::class)->findOne([
            'name' => 'admin.event_webhook'
        ]);

        if (!$permission) {
            $permission = new Permission;
            $permission->name = 'admin.event_webhook';
            $permission->desc = 'eventwebhook.perm_desc';

            transaction($permission)->run();
        }

        return true;
    }

    public function uninstall(\Flute\Core\Modules\ModuleInformation &$module): bool
    {
        $permission = rep(Permission::class)->findOne([
            'name' => 'admin.event_webhook'
        ]);

        if ($permission) {
            transaction($permission, 'delete')->run();
        }

        return true;
    }
}