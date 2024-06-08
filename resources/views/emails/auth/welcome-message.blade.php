<x-mail::message>
    <p>Dear Ms/Mr/Mrs,</p>
    <p>Greetings from iTrust Finance,</p>
    <p>Please be informed we have successfully set up your trading account with us.</p>
    <p>We have created your DSE CDS number, which is {{$user->dse_account}}.</p>
    <p>To begin purchasing shares or bonds, kindly deposit your funds to our DSE Trust Account with the below details then email us your purchase order request.</p>
    <p>Bank Name: NBC Limited</p>
    <p>Account Name: iTrust Finance Limited</p>
    <p>Account Number: 011103041540</p>
    <p>Branch: Corporate Branch</p>
    <p>In case you want to sell your shares or bonds kindly email us and we will be happy to facilitate your request.</p>
    <p>Thank you for trusting us and looking forward to building your wealth.</p>
    <p>Happy Trading!</p>



    {{--    Welcome to iTrust. We are excited to walk with you on your financial journey.--}}
{{--    Below are your account details--}}
{{--    Your Trading Account Number: {{$user->uid}}--}}
{{--    Your Username: {{$user->email}}--}}
{{--    Follow these simple steps to start investing.--}}
{{--    1.	Login into your <a href="{{env("APP_URL")}}">account</a>--}}
{{--    2.	<a href="{{env("APP_URL")}}">Place Your Order</a>--}}
{{--    And that's it!--}}

</x-mail::message>
