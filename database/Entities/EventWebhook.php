<?php

namespace Flute\Modules\EventWebhook\database\Entities;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;

/**
 * @Entity()
 */
class EventWebhook
{
    /** @Column(type="primary") */
    public $id;

    /** @Column(type="string") */
    public $event;

    /** @Column(type="string") */
    public $webhook_url;

    /** @Column(type="string") */
    public $webhook_name;

    /** @Column(type="string", nullable=true) */
    public $webhook_avatar;

    /** @Column(type="text") */
    public $content;
    
    /** @Column(type="json", nullable=true) */
    public $embeds;
}
