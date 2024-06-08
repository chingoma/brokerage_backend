<?php

namespace App\Helpers;

use App\Jobs\SendNewLetterTest;
use App\Mail\Clients\WelcomeEmailMailable;
use App\Mail\Orders\OverdraftBondPlaced;
use App\Mail\Orders\OverdraftOrderPlaced;
use App\Models\MarketReports\MarketCustomReport;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class EmailsHelper
{
    public static function sendOverdraftEmail(string $type): void
    {

        if ($type == 'bond') {
            $mailable = new OverdraftBondPlaced('');
            \Mail::to(Helper::mailingListOverdraft())->queue($mailable);
        }

        if ($type == 'equity') {
            $mailable = new OverdraftOrderPlaced('');
            \Mail::to(Helper::mailingListOverdraft())->queue($mailable);
        }
    }

    public static function newCustomerAdmin(User $user): void
    {
        $welcomeMailable = new WelcomeEmailMailable($user);
        Mail::to($user)->queue($welcomeMailable);
    }

    public static function newsLetter(): void
    {
        $report = MarketCustomReport::first();
        if (! empty($report)) {
            SendNewLetterTest::dispatchAfterResponse($report);
        }
    }
}
