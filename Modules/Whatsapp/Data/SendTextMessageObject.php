<?php

namespace Modules\Whatsapp\Data;

use stdClass;

class SendTextMessageObject
{
    public string $messaging_product = 'whatsapp';

    public bool $preview_url = false;

    public string $type = 'text';

    public string $message;

    public string $recipient_type = 'individual';

    public mixed $recipient = '';

    public function __construct(
        string $recipient,
        string $message,
    ) {
        $this->message = $message;
        $this->recipient = $recipient;
    }

    public function message(): string
    {

        $text = new stdClass();
        $text->preview_url = $this->preview_url;
        $text->body = $this->message;

        $message = new stdClass();
        $message->messaging_product = $this->messaging_product;
        $message->recipient_type = $this->recipient_type;
        $message->to = $this->recipient;
        $message->type = $this->type;
        $message->text = $text;

        return json_encode($message);
    }
}
