<?php

namespace Modules\FlexcubeAPI\DTOs;

use WendellAdriel\ValidatedDTO\SimpleDTO;

class MultioffsetmaterFull extends SimpleDTO
{
   public string $DE_BATCH_NUMBER; // Batch Number BL001 - BL099
   public string $DE_CURRNO; // DE Incremental Current  Number
   public string $DE_CCY_CD; // DE Currency code
   public string $DE_MAIN; // Main Transaction
   public string $DE_OFFSET; // Offset Branch [ branch pre-defined ] by default 002
   public string $DE_VALUE_DATE; // when trade executed ? [ yyyy-mm-dd]
   public string $DE_DR_CR; // Debit or Credit [D/C]
   public string $DE_AMOUNT; // Amount
   public string $DE_EXCH_RATE; // always 1 for TZS
   public string $DE_LCY_AMOUNT; // DE_AMOUNT * DE_EXCH_RATE
   public string $DE_INSTRUMENT_NUMBER; // Transaction Reference Number
   public string $MAKERID; // will be provided
   public string $DE_AUTHORIZED_BY; // whou authorize (will be provided )
   public string $DE_DATETIME; // Current Time (time of transactin )
   public  $AUTHSTAT; // Authorisation status 'by default Null'
   public string $DE_MAKER_DATETIME;
   public string $DE_DESCRIPTION; // Description
   public string $DE_ADDL_TEXT; // From statement
   public string $DE_BATCH_DESC; // Batch Description
   public string $DE_ACCNO;
   public Mltoffsetdetail | array | null $Mltoffsetdetail;

    protected function defaults(): array
    {
        return  [
            'AUTHSTAT' => '',
        ];
    }

    protected function casts(): array
    {
        return  [];
    }
}
