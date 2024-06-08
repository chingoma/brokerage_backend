<?php

namespace Modules\NIDA\Helpers;

class iRequest
{
    //private $Header,$Body;
    public function __construct($header, $body)
    {
        $this->Header = $header;
        $this->Body = $body;
    }


}
