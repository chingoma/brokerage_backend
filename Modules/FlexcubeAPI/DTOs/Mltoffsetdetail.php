<?php

namespace Modules\FlexcubeAPI\DTOs;

use WendellAdriel\ValidatedDTO\SimpleDTO;

class Mltoffsetdetail extends SimpleDTO
{

   public string $DE_INSTRUMENT_NUMBER;
   public string $DE_AMOUNT;
   public string $DE_BRANCH_CODE;
   public string $DE_SERIAL_NUMBER; // Batch Number BL001 - BL099
   public string $ACCOUNT_DESCRIPTION; // DE Incremental Current  Number
   public string $DE_ACCNO;

    protected function defaults(): array
    {
        return  [];
    }

    protected function casts(): array
    {
        return  [];
    }
}
