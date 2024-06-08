<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1" name="viewport">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="telephone=no" name="format-detection">
    <title></title>
    <!--[if (mso 16)]>
    <style type="text/css">
        a {text-decoration: none;}
    </style>
    <![endif]-->
    <!--[if gte mso 9]><style>sup { font-size: 100% !important; }</style><![endif]-->
    <!--[if gte mso 9]>
    <xml>
        <o:OfficeDocumentSettings>
            <o:AllowPNG></o:AllowPNG>
            <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
    </xml>
    <![endif]-->
    <!--[if !mso]>
    <!-- -->
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Manrope&display=swap" rel="stylesheet">
    <!--<![endif]-->
</head>

<body>
<div class="es-wrapper-color">
    <!--[if gte mso 9]>
    <v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t">
        <v:fill type="tile" color="#ffffff"></v:fill>
    </v:background>
    <![endif]-->
    <table class="es-wrapper" width="100%" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
            <td class="esd-email-paddings" valign="top">
                <table cellpadding="0" cellspacing="0" class="es-content" align="center">
                    <tbody>
                    <tr>
                        <td class="esd-stripe" align="center" background="https://ssnzvs.stripocdn.email/content/guids/CABINET_cc952a79d6ea8e3c503431d548249b8c/images/frame_29_QFf.png" style="background-image: url(https://ssnzvs.stripocdn.email/content/guids/CABINET_cc952a79d6ea8e3c503431d548249b8c/images/frame_29_QFf.png); background-repeat: no-repeat; background-position: center bottom;">
                            <table bgcolor="#ffffff" class="es-content-body" align="center" cellpadding="0" cellspacing="0" width="500">
                                <tbody>
                                <tr>
                                    <td class="esd-structure es-p30t es-p20b es-p20r es-p20l" align="left">
                                        <table cellpadding="0" cellspacing="0" width="100%">
                                            <tbody>
                                            <tr>
                                                <td width="460" class="esd-container-frame" align="center" valign="top">
                                                    <table cellpadding="0" cellspacing="0" width="100%">
                                                        <tbody>
                                                        <tr>
                                                            <td align="center" class="esd-block-text">
                                                                <h1>Overdraft Orders</h1>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td align="center" class="esd-block-text es-p15t">
                                                                <p style="font-size: 14px; line-height: 150%;">Below is the list of pending approval <b>Overdraft Orders</b></p>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="esd-structure esdev-adapt-off es-p20" align="left">
                                        <table cellpadding="0" cellspacing="0" width="100%">
                                            <tbody>
                                            <tr>
                                                <td width="500" class="esd-container-frame" align="center" valign="top">
                                                    <table cellpadding="0" cellspacing="0" width="100%" bgcolor="#ffffff" style="background-color: #ffffff; border-radius: 10px; border-collapse: separate;">
                                                        <tbody>
                                                        <tr>
                                                            <td align="center" class="esd-block-text" esd-links-underline="none">
                                                                <table border="1" bordercolor="#cccccc" align="center" cellspacing="5" cellpadding="5" style="height:100px;width:500px;" class="es-table">
                                                                    <tbody>
                                                                    <tr style="background-color: #E0E1FD; height:50px">
                                                                        <td style="font-family: manrope, arial, sans-serif; text-align: center;">&nbsp;<span style="font-size:14px;">#&nbsp;</span></td>
                                                                        <td style="font-family: manrope, arial, sans-serif; text-align: center; font-size: 14px;">CUSTOMER</td>
                                                                        <td style="font-family: manrope, arial, sans-serif; text-align: center; font-size: 14px;">Volume</td>
                                                                        <td style="font-family: manrope, arial, sans-serif; text-align: center; font-size: 14px;">Amount</td>
                                                                        <td style="font-family: manrope, arial, sans-serif; text-align: center; font-size: 14px;">Time</td>
                                                                    </tr>
                                                                    @php
                                                                        $orders = \Modules\Orders\Entities\Order::where("status","overdraft")->orderBy("created_at")->get()
                                                                    @endphp
                                                                    @if(!empty($orders))
                                                                        @foreach($orders as $key => $order)
                                                                            <tr style="height:50px">
                                                                                <td style="text-align: center; font-family: manrope, arial, sans-serif; font-size: 12px;">{{$key + 1}}</td>
                                                                                <td style="text-align: center; font-family: manrope, arial, sans-serif; font-size: 12px;">{{$order->client->name}}</td>
                                                                                <td style="text-align: center; font-family: manrope, arial, sans-serif; font-size: 12px;">{{number_format($order->volume,2)}}</td>
                                                                                <td style="text-align: center; font-family: manrope, arial, sans-serif; font-size: 12px;">{{number_format($order->amount,2)}}</td>
                                                                                <td style="text-align: center; font-family: manrope, arial, sans-serif; font-size: 12px;">{{$order->date}}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    @endif
                                                                    </tbody>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
</div>
</body>

</html>
