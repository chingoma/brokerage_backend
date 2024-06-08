@component('mail::message')

> **Dear {{ $name }}**, {{ $amount }}<br/>
> **Credited to your account** {{ $account }}<br/>
> **Rmks** {{ $remark }}<br/>
> **By** {{ $datetime }}<br/>
> **New Balance** {{ $balance }}<br/>
For any queries contact  us 255 746 25 1394<br/>
@lang('Regards,')<br/>
{{ config('app.name') }}
@endcomponent
