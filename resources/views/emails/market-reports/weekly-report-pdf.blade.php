<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title></title>
    <style>

        body {
            font-family: "Nunito", sans-serif;);
        }

        th {
            border-bottom: 1px solid #d4005c;
        }
    </style>

    <style>
        .hover-underline:hover {
            text-decoration: underline !important;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        @keyframes ping {

            75%,
            100% {
                transform: scale(2);
                opacity: 0;
            }
        }

        @keyframes pulse {
            50% {
                opacity: .5;
            }
        }

        @keyframes bounce {

            0%,
            100% {
                transform: translateY(-25%);
                animation-timing-function: cubic-bezier(0.8, 0, 1, 1);
            }

            50% {
                transform: none;
                animation-timing-function: cubic-bezier(0, 0, 0.2, 1);
            }
        }

        @media (max-width: 600px) {
            .sm-leading-32 {
                line-height: 32px !important;
            }

            .sm-px-24 {
                padding-left: 24px !important;
                padding-right: 24px !important;
            }

            .sm-py-32 {
                padding-top: 32px !important;
                padding-bottom: 32px !important;
            }

            .sm-w-full {
                width: 100% !important;
            }
        }
    </style>
</head>
<body >
<div id="email" style="width:600px;margin: auto;">
    <!-- Header -->

    <table role="presentation" border="0" width="100%" cellspacing="0">
        <tr>
            <td  align="center" style="color: white;">
                <img alt="Flower" src="{{asset("business/header.png")}}"  style="margin-top: 20px; width: 100%;" >
            </td>
        </tr>

    </table>

    <table role="presentation" border="0" width="100%" cellspacing="0">
        <tr>
            <td style="padding: 3px 3px 3px 3px;">
                <h3>@php echo nl2br($options['report']->title); @endphp</h3>
            </td>
        </tr>
    </table>

    <!-- Body 1 -->
    <div style="padding: 10px;">
        <table role="presentation" border="0" width="100%" cellspacing="0">
            <tr>
                <td style="padding: 3px 3px 3px 3px;">
                    <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Avenir">@php echo nl2br($options['report']->description); @endphp</p>
                </td>
            </tr>
        </table>

        <table role="presentation" border="0" width="100%" cellspacing="0">
            <thead>
            <tr>
                <th align="left">Equity Overview</th>
                <th align="left">Previous</th>
                <th align="left">Current</th>
                <th align="left">Change %</th>
            </tr>
            </thead>
            @if(!empty($options['overview']))
                @foreach ($options['overview'] as $y => $value)
                    <tr>
                        <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{$value['equity_overview']}}</td>
                        <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{number_format($value['previous'])}}</td>
                        <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{number_format($value['current'])}}</td>
                        <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{$value['change']}}</td>
                    </tr>
                @endforeach
            @endif
        </table>
    </div>

    <p style="height: 15px"></p>

    <div style="width: 100%; background: #003756; color: white; font-size: 14px;">
        <div style="margin: 10px">
            <div style="height: 15px"></div>
            <h3>Market Statistics<br/>
                <small>Equities Summary</small>
            </h3>
            <table role="presentation" border="0" width="100%" cellspacing="0">
                <thead>
                <tr>
                    <th style="text-align: left;">Companies</th>
                    <th align="left">Week Close (TZS)</th>
                    <th align="left">Change %</th>
                    <th align="left">Turnover (TZS)</th>
                </tr>
                </thead>
                @if(!empty($options['summary']))
                    @foreach ($options['summary'] as $y => $value1)
                        <tr>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{$value1['company']}}</td>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{number_format($value1['week_close'])}}</td>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{$value1['change']}}</td>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{number_format($value1['turnover'])}}</td>
                        </tr>
                    @endforeach
                @endif
            </table>
            <div style="height: 40px"></div>

            <h3>Weekly Top Gainers </h3>
            <table role="presentation" border="0" width="100%" cellspacing="0">
                <thead>
                <tr>
                    <th style="text-align: left;">Companies</th>
                    <th align="left">Change %</th>
                    <th align="left">Price (TZS)</th>
                </tr>
                </thead>
                @if(!empty($options['gainers']))
                    @foreach ($options['gainers'] as $y => $value)
                        <tr>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{$value['company']}}</td>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{$value['change']}}</td>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{number_format($value['week_close'])}}</td>
                        </tr>
                    @endforeach
                @endif
            </table>
            <div style="height: 40px"></div>

            <h3>Weekly Top Losers</h3>
            <table role="presentation" border="0" width="100%" cellspacing="0">
                <thead>
                <tr>
                    <th style="text-align: left;">Companies</th>
                    <th align="left">Change %</th>
                    <th align="left">Price (TZS)</th>
                </tr>
                </thead>
                @if(!empty($options['losers']))
                    @foreach ($options['losers'] as $y => $value)
                        <tr>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{$value['company']}}</td>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{$value['change']}}</td>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{number_format($value['week_close'])}}</td>
                        </tr>
                    @endforeach
                @endif
            </table>
            <div style="height: 40px"></div>

            <h3>Weekly Top Movers</h3>
            <table role="presentation" border="0" width="100%" cellspacing="0">
                <thead>
                <tr>
                    <th style="text-align: left;">Companies</th>
                    <th align="left">Turnover (TZS)</th>
                    <th align="left">% of Total Weekly Turnover</th>
                </tr>
                </thead>
                @if(!empty($options['movers']))
                    @foreach ($options['movers'] as $y => $value)
                        @if($value['turnover'] > 0)
                        <tr>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{$value['company']}}</td>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{number_format($value['turnover'])}}</td>
                            @php
                                    $percent = $value['turnover']/$options['totalTurnover'] * 100;
                                @endphp
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{round($percent,2)}}</td>
                        </tr>
                        @endif
                    @endforeach
                @endif
            </table>

            @if(!empty($options['governmentBonds']) || !empty($options['cooperateBonds']))
                <div style="height: 40px"></div>
                <h3>Bond Summary.</h3>
            @endif

            @if(!empty($options['governmentBonds']))
                <h5>Government Bond</h5>
                <table role="presentation" border="0" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th style="text-align: left;">Duration</th>
                        <th align="left">Prices</th>
                        <th align="left">W.A.Y %</th>
                        <th align="left">Amount (TZS Bln)</th>
                    </tr>
                    </thead>
                        @if (!empty($options['bond25']))
                            <tr>
                                <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">25 Years</td>
                                @if(round($options['bond25']['low'],4) == round($options['bond25']['high'],4))
                                    {{round($options['bond25']['low'],4)}}
                                @else
                                <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{round($options['bond25']['low'],4)}} - {{round($options['bond25']['high'],4)}}</td>
                                @endif
                                <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{round($options['bond25']['yield'],4)}}</td>
                                <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{round($options['bond25']['amount'],4)}}</td>
                            </tr>
                        @endif
                    @if (!empty($options['bond20']))
                        <tr>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">20 Years</td>
                            @if(round($options['bond20']['low'],4) == round($options['bond20']['high'],4))
                                {{round($options['bond20']['low'],4)}}
                            @else
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{round($options['bond20']['low'],4)}} - {{round($options['bond20']['high'],4)}}</td>
                            @endif
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{round($options['bond20']['yield'],4)}}</td>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{round($options['bond20']['amount'],4)}}</td>
                        </tr>
                    @endif

                    @if (!empty($options['bond15']))
                        <tr>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">15 Years</td>
                            @if(round($options['bond15']['low'],4) == round($options['bond15']['high'],4))
                                {{round($options['bond15']['low'],4)}}
                            @else
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{round($options['bond15']['low'],4)}} - {{round($options['bond15']['high'],4)}}</td>
                            @endif
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{round($options['bond15']['yield'],4)}}</td>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{round($options['bond15']['amount'],4)}}</td>
                        </tr>
                    @endif

                    @if (!empty($options['bond10']))
                        <tr>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">10 Years</td>
                            @if(round($options['bond10']['low'],4) == round($options['bond10']['high'],4))
                                {{round($options['bond10']['low'],4)}}
                            @else
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{round($options['bond10']['low'],4)}} - {{round($options['bond10']['high'],4)}}</td>
                            @endif
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{round($options['bond10']['yield'],4)}}</td>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{round($options['bond10']['amount'],4)}}</td>
                        </tr>
                    @endif

                    @if (!empty($options['bond7']))
                        <tr>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">7 Years</td>
                            @if(round($options['bond7']['low'],4) == round($options['bond7']['high'],4))
                                {{round($options['bond7']['low'],4)}}
                            @else
                                <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{round($options['bond7']['low'],4)}} - {{round($options['bond7']['high'],4)}}</td>
                            @endif
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{round($options['bond7']['yield'],4)}}</td>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{round($options['bond7']['amount'],4)}}</td>
                        </tr>
                    @endif

                    @if (!empty($options['bond5']))
                        <tr>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">5 Years</td>
                            @if(round($options['bond5']['low'],4) == round($options['bond5']['high'],4))
                                {{round($options['bond5']['low'],4)}}
                            @else
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{round($options['bond5']['low'],4)}} - {{round($options['bond5']['high'],4)}}</td>
                            @endif
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{round($options['bond5']['yield'],4)}}</td>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{round($options['bond5']['amount'],4)}}</td>
                        </tr>
                    @endif

                    @if (!empty($options['bond2']))
                        <tr>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">5 Years</td>
                            @if(round($options['bond2']['low'],4) == round($options['bond2']['high'],4))
                                {{round($options['bond2']['low'],4)}}
                            @else
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{round($options['bond2']['low'],4)}} - {{round($options['bond2']['high'],4)}}</td>
                            @endif
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{round($options['bond2']['yield'],4)}}</td>
                            <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{round($options['bond2']['amount'],4)}}</td>
                        </tr>
                    @endif
                </table>
            @endif

            @if(!empty($options['cooperateBonds']) && count($options['cooperateBonds']) > 0)
                <div style="height: 20px"></div>
                <table role="presentation" border="0" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th style="text-align: left;">CORPORATE BOND</th>
                        <th align="left">Prices</th>
                        <th align="left">Yield</th>
                        <th align="left">Amount</th>
                    </tr>
                    </thead>
                        @foreach ($options['cooperateBonds'] as $y => $value)
                            <tr>
                                <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{$value['bond_no']}} <br/> {{$value['duration']}} Years</td>
                                <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{$value['week_close']}}</td>
                                <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{$value['coupon']}}</td>
                                <td style="vertical-align: middle; padding: 3px 3px 3px 3px;">{{$value['yield']}}</td>
                            </tr>
                        @endforeach
                </table>
                <div style="height: 60px"></div>
            @endif
        </div>
    </div>

</div>

</body>
</html>
