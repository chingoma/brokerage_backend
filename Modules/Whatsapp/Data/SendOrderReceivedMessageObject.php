<?php

namespace Modules\Whatsapp\Data;

use stdClass;

class SendOrderReceivedMessageObject
{
    public string $messaging_product = 'whatsapp';

    public string $type = 'template';

    public string $template_name = 'order_received';

    public string $languageCode = 'en_US';

    public string $order_type = '';

    public string $name = '';

    public mixed $recipient = '';

    public function __construct(
        string $name,
        string $order_type,
        string $recipient
    ) {
        $this->name = $name;
        $this->order_type = $order_type;
        $this->recipient = $recipient;
    }

    public function message(): string
    {

        $language = new stdClass();
        $language->code = $this->languageCode;

        $template = new stdClass();
        $template->name = $this->template_name;
        $template->language = $language;

        $payload = new stdClass();
        $payload->messaging_product = $this->messaging_product;
        $payload->to = $this->recipient;
        $payload->type = $this->type;
        $payload->template = $template;

        return json_encode($payload);
    }
}
