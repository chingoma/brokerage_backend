<?php

namespace Modules\Whatsapp\Data;

use stdClass;

class SendTwoFactorMessageObject
{
    public string $messaging_product = 'whatsapp';

    public string $type = 'template';

    public string $template_name = '';

    public string $languageCode = 'en_US';

    public string $message = '';

    public mixed $recipient = '';

    public function __construct(
        string $template_name,
        string $message,
        string $recipient
    ) {
        $this->template_name = $template_name;
        $this->message = $message;
        $this->recipient = $recipient;
    }

    public function message(): string
    {

        $language = new stdClass();
        $language->code = $this->languageCode;

        $template = new stdClass();
        $template->name = $this->template_name;
        $template->language = $language;
        $template->components = [
            [
                'type' => 'body',
                'parameters' => [
                    [
                        'type' => 'text',
                        'text' => $this->message,
                    ],
                ],
            ],
            [
                'type' => 'button',
                'sub_type' => 'url',
                'index' => '0',
                'parameters' => [
                    [
                        'type' => 'text',
                        'text' => $this->message,
                    ],
                ],

            ],
        ];

        $payload = new stdClass();
        $payload->messaging_product = $this->messaging_product;
        $payload->to = $this->recipient;
        $payload->type = $this->type;
        $payload->template = $template;

        return json_encode($payload);
    }
}
