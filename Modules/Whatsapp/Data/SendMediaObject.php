<?php

namespace Modules\Whatsapp\Data;

use stdClass;

class SendMediaObject
{
    public string $messaging_product = 'whatsapp';

    public string $file = '';

    public string $type = '';

    public function __construct(
        string $file,
        string $type
    ) {
        $this->file = $file;
        $this->type = $type;
    }

    public function message(): string
    {

        $payload = new stdClass();
        $payload->messaging_product = $this->messaging_product;
        $payload->type = $this->type;
        $payload->file = $this->file;

        return json_encode($payload);
    }
}
