<?php

namespace App\Helpers\Clients;

use App\Helpers\Helper;
use App\Mail\Clients\AccountVerifyMailable;
use App\Mail\Clients\PasswordChangedMailable;
use App\Mail\Clients\ResetPasswordMailable;
use App\Mail\Clients\SendActivationMailable;
use App\Mail\Clients\WelcomeEmailMailable;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailsHelper
{

    public static function password_reset_link(User $user, string $token, Request $request): void
    {
        try {
            $data['url'] = env('CLIENT_URL').'/auth/reset-password/'.$token;
            $data['ip'] = $request->ip();
            $mailable = new ResetPasswordMailable($user,$data);
            Mail::to($user->email)->queue($mailable);
        }catch (Throwable $throwable){
            report($throwable);
        }

    }

    public static function send_password_reset_link_admin(User $user, string $token, Request $request): void
    {
        try {
            $data['url'] = getenv('ADMIN_URL').'/#/auth/reset-password/'.$token;
            $data['ip'] = $request->ip();
            $mailable = new ResetPasswordMailable($user,$data);
            Mail::to($user->email)
                ->queue($mailable);
        }catch (Throwable $throwable){
            report($throwable);
        }

    }

    public static function send_password_reset_link(User $user, string $token, Request $request): void
    {
        try {
            $data['url'] = env('CLIENT_URL').'/auth/reset-password/'.$token;
            $data['ip'] = $request->ip();
            $mailable = new ResetPasswordMailable($user,$data);
            Mail::to($user->email)
                ->queue($mailable);
        }catch (Throwable $throwable){
            report($throwable);
        }

    }

    public static function send_activation_email(User $user, string $token, Request $request): void
    {
        try {
            $data['url'] = env('CLIENT_URL').'/reset-password/'.$token.'?email='.$user->email;
            $data['ip'] = $request->ip();
            $mailable = new SendActivationMailable($user,$data);
            Mail::to($user->email)
                ->queue($mailable);
        }catch (Throwable $throwable){
            report($throwable);
        }
    }

    public static function send_verification_email(User $user): void
    {
        try{
            $data['verify_url'] = env('CLIENT_URL').'/auth/verify-account/'.$user->verify_token;
            $data['name'] = $user->name;
            $data['support_email'] = "bbo.support@lockminds.com";
            $data['logo'] = "https://lockminds.com/resources/web/images/logo.png";
            $data['facebook'] = "https://www.facebook.com";
            $data['twitter'] = "https://twitter.com";
            $data['instagram'] = "https://www.instagram.com";
            $mailable = new AccountVerifyMailable($user,$data);
            Mail::to($user->email)
                ->queue($mailable);
        }catch (Throwable $throwable){
            report($throwable);
        }
    }

    public static function send_welcome_email(User $user): void
    {
        try {
            $business = Helper::business();
            $data['url'] = env('CLIENT_URL');
            $data['support_email'] = $business->email;
            $data['name'] = $user->profile->name;
            $data['logo'] = $business->logo;
            $data['facebook'] = $business->facebook;
            $data['twitter'] = $business->twitter;
            $data['instagram'] = $business->instagram;
            $mailable = new WelcomeEmailMailable($user,$data);
            Mail::to($user->email)
                ->queue($mailable);
        }catch (Throwable $throwable){
            report($throwable);
        }
    }

    public static function password_changed(User $user): void
    {
        try{
            $business = Helper::business();
            $data['url'] = env('CLIENT_URL');
            $data['support_email'] = $business->email;
            $data['name'] = $user->profile->name;
            $data['logo'] = $business->logo;
            $data['facebook'] = $business->facebook;
            $data['twitter'] = $business->twitter;
            $data['instagram'] = $business->instagram;
            $mailable = new PasswordChangedMailable($user,$data);
            Mail::to($user->email)
                ->queue($mailable);
       }catch (Throwable $throwable){
        report($throwable);
      }
    }
}
