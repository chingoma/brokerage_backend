<?php

namespace Modules\Whatsapp\Data;

use stdClass;

class SendWalletCreditedObject
{
    public string $messaging_product = 'whatsapp';

    public string $type = 'template';

    public string $template_name = 'wallet_credited';

    public string $languageCode = 'en_US';

    public mixed $recipient = '';

    public mixed $name = '';

    public mixed $amount = '';

    public mixed $account = '';

    public mixed $remark = '';

    public mixed $datetime = '';

    public mixed $balance = '';

    /**
     * @param  mixed|string  $recipient
     * @param  mixed|string  $name
     * @param  mixed|string  $amount
     * @param  mixed|string  $account
     * @param  mixed|string  $remark
     * @param  mixed|string  $datetime
     * @param  mixed|string  $balance
     */
    public function __construct(mixed $recipient, mixed $name, mixed $amount, mixed $account, mixed $remark, mixed $datetime, mixed $balance)
    {
        $this->recipient = $recipient;
        $this->name = $name;
        $this->amount = $amount;
        $this->account = $account;
        $this->remark = $remark;
        $this->datetime = $datetime;
        $this->balance = $balance;
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
                        'text' => $this->name.', '.$this->amount,
                    ],
                    [
                        'type' => 'text',
                        'text' => $this->account,
                    ],
                    [
                        'type' => 'text',
                        'text' => $this->remark,
                    ],
                    [
                        'type' => 'text',
                        'text' => $this->datetime,
                    ],
                    [
                        'type' => 'text',
                        'text' => $this->balance,
                    ],
                    [
                        'type' => 'text',
                        'text' => getenv('SUPPORT_NUMBER'),
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
