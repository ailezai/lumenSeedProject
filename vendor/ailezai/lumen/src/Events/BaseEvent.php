<?php

namespace AiLeZai\Lumen\Framework\Events;

use Illuminate\Queue\SerializesModels;

abstract class BaseEvent
{
    use SerializesModels;
}