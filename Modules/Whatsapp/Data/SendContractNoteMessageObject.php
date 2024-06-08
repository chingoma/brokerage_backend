<?php

namespace Modules\Whatsapp\Data;

use stdClass;

class SendContractNoteMessageObject
{
    public string $messaging_product = 'whatsapp';

    public string $type = 'template';

    public string $template_name = '';

    public string $languageCode = 'en_US';

    public string $content = '';

    public mixed $recipient = '';

    public function __construct(
        string $template_name,
        string $recipient,
        string $content,
        string $messaging_product = 'whatsapp',
        string $type = 'template',
        string $languageCode = 'en_US',
    ) {
        $this->messaging_product = $messaging_product;
        $this->type = $type;
        $this->template_name = $template_name;
        $this->languageCode = $languageCode;
        $this->content = $content;
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
                        'text' => $this->content,
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
                        'text' => $this->content,
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
