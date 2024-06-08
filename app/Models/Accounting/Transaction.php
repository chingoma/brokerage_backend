<?php

namespace App\Models\Accounting;

use App\Models\DealingSheet;
use App\Models\MasterModel;
use App\Traits\UuidForKey;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Bonds\Entities\BondExecution;
use Modules\Orders\Entities\Order;
use OwenIt\Auditing\Contracts\Auditable;
use Thinktomorrow\DynamicAttributes\HasDynamicAttributes;

class Transaction extends MasterModel implements Auditable
{
    use HasDynamicAttributes;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;
    use UuidForKey;

    protected $dynamicKeys = ['reject_resoan'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected $appends = [
        'client',
        'files',
        'account',
        'method',
        'account_category',
        'bank',
        'order',
        'trade',
        'custodian_type',
    ];

    public function getCustodianTypeAttribute()
    {
        $type = '';
        if (strtolower($this->getAttribute('category')) == 'custodian') {
            $type = 'equity';
            $order = DB::table('bond_orders')->find($this->getAttribute('order_id'));
            if (!empty($order)) {
                $type = 'bond';
            }
        }

        return $type;
    }

    public function getAmountAttribute($value)
    {

        if (strtolower($this->getAttribute('category')) == 'order' || strtolower($this->getAttribute('category')) == 'custodian') {
            $sheet = DealingSheet::where('slip_no', $this->getAttribute('reference'))->first();

            return $sheet->payout ?? 0;
        } elseif (strtolower($this->getAttribute('category')) == 'bond') {
            $sheet = BondExecution::where('slip_no', $this->getAttribute('reference'))->first();

            return $sheet->payout ?? 0;
        } else {
            return $value;
        }

    }

    public function getAccountCategoryAttribute()
    {
        return DB::table('account_categories')->find($this->getAttribute('account_category_id'));
    }

    public function getOrderAttribute()
    {
        if (strtolower($this->getAttribute('category')) == 'order') {
            return DB::table('orders')->find($this->getAttribute('order_id'));
        }
        if (strtolower($this->getAttribute('category')) == 'bond') {
            return DB::table('bond_orders')->find($this->getAttribute('order_id'));
        }
        if (strtolower($this->getAttribute('category')) == 'custodian') {
            $order = DB::table('bond_orders')->find($this->getAttribute('order_id'));
            if (empty($order)) {
                $order = DB::table('orders')->find($this->getAttribute('order_id'));
            }

            return $order;
        }

        return '';
    }

    public function getTradeAttribute()
    {
        if (strtolower($this->getAttribute('category')) == 'order') {
            return DealingSheet::where('slip_no', $this->getAttribute('reference'))->first();
        }
        if (strtolower($this->getAttribute('category')) == 'custodian') {
            $sheet = DealingSheet::where('slip_no', $this->getAttribute('reference'))->first();
            if (empty($sheet)) {
                $sheet = BondExecution::where('slip_no', $this->getAttribute('reference'))->first();
            }

            return $sheet;
        }
        if (strtolower($this->getAttribute('category')) == 'bond') {
            return BondExecution::where('slip_no', $this->getAttribute('reference'))->first();
        }

        return '';
    }

    public function getBankAttribute()
    {
        return DB::table('real_accounts')->find($this->getAttribute('real_account_id'));
    }

    public function getClientAttribute()
    {

        //            if(strtolower($this->getAttribute("category")) == "order") {
        //                $sheet = DB::table("orders")->find($this->getAttribute("order_id"));
        //                if(empty($sheet)){
        //                    $sheet = DB::table("bond_orders")->find($this->getAttribute("order_id"));
        //                }
        //                if(empty($sheet->client_id)) {
        //                    Log::error("trans id =" . $this->getAttribute("id") . " Order id " . $this->getAttribute("order_id") . " client_id " . $this->getAttribute("client_id"));
        //                }
        //                return DB::table("users")->find($sheet->client_id);
        //            }
        //
        //            if (strtolower($this->getAttribute("category")) == "bond") {
        //                $sheet = DB::table("bond_orders")->find($this->getAttribute("order_id"));
        //                return DB::table("users")->find($sheet->client_id);
        //            }
        //
        //            if (strtolower($this->getAttribute("category")) == "custodian"){
        //                $sheet = DB::table("orders")->find($this->getAttribute("order_id"));
        //                if(empty($sheet)){
        //                    $sheet = DB::table("bond_orders")->find($this->getAttribute("order_id"));
        //                }
        //                return DB::table("users")->find($sheet->client_id??"");
        //            }

        return DB::table('users')->find($this->getAttribute('client_id'));

    }

    public function getMethodAttribute()
    {
        return DB::table('payment_methods')->find($this->getAttribute('payment_method_id'));
    }

    public function getFilesAttribute()
    {
        return TransactionFile::where('transaction_id', $this->getAttribute('id'))->get();
    }

    public function getAccountAttribute()
    {
        return DB::table('accounts')->find($this->getAttribute('account_id'));
    }

    public function scopeReceipts($query)
    {
        return $query->groupBy('reference')
            ->whereNull('is_journal')
            ->where('category', 'receipt');
    }

    public function scopeReceiptsReport($query)
    {
        return $query->where('category', 'Receipt')->where('status', 'approved');
    }

    public function scopeVouchers($query)
    {
        return $query->whereNull('is_journal')->where('category', 'Voucher')->groupBy('reference');
    }

    public function scopeJournals($query)
    {
        return $query->whereNotNull('is_journal')->groupBy('reference');
    }

    public function scopePayments($query)
    {
        return $query->whereNull('is_journal')->where('category', 'Payment')->groupBy('reference');
    }

    public function scopePaymentsReport($query)
    {
        return $query->where('category', 'Payment')->where('status', 'approved');
    }

    public function scopeExpenses($query)
    {
        return $query->whereNull('is_journal')->where('category', 'Expense')->groupBy('reference');
    }

    public function scopeExpensesReport($query)
    {
        return $query->where('category', 'Expense')->where('status', 'approved');
    }
}
