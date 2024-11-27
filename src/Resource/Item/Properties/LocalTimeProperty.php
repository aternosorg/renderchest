<?php

namespace Aternos\Renderchest\Resource\Item\Properties;

use DateTime;
use DateTimeZone;
use Exception;
use stdClass;

class LocalTimeProperty extends StringProperty
{
    public function __construct()
    {
        parent::__construct("minecraft:local_time", "");
    }

    /**
     * @inheritDoc
     */
    public function get(stdClass $options): string
    {
        try {
            $timezone = new DateTimeZone($options->timezone ?? "");
            $time = new DateTime("now", $timezone);
            return $time->format($options->pattern ?? "");
        } catch (Exception) {
            return "";
        }
    }
}
