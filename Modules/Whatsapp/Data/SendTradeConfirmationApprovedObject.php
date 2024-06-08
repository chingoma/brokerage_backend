<?php

namespace Modules\Whatsapp\Data;

use stdClass;

class SendTradeConfirmationApprovedObject
{
    public string $messaging_product = 'whatsapp';

    public string $type = 'template';

    public string $template_name = 'trade_confirmation_approved';

    public string $languageCode = 'en_US';

    public mixed $recipient = '';

    public function __construct(
        string $recipient
    ) {
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
