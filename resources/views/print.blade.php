<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{$order->id}}</title>
    <style>
        .printer {
            margin: auto;
        }

        .table {
            width: 205mm;
            border: 1pt solid #000;
            border-collapse: collapse;
        }

        tr, td {
            border: 1pt solid #000;
        }

        .bg-blue {
            background-color: #1e40af;
            color: #FFF;
        }

        @media print {
            .printer {
                width: 210mm;
            }

            .table {
                width: 205mm;
                border: 1pt solid #000;
                border-collapse: collapse;
            }

            .bg-blue {
                background-color: #1e40af;
                color: #FFF;
            }

            tr, td {
                border: 1pt solid #000;
            }
        }
    </style>
</head>
<body>
<div class="printer">
    <table class="table">
        <tr>
            <td colspan="2" rowspan="3"><img src="" alt=""></td>

        </tr>
        <tr>
            <td><span>التاريخ</span></td>
            <td><span>{{$order->shipping_date->format('Y-m-d')}}</span></td>

        </tr>
        <tr>
            <td><span>للتواصل</span></td>
            <td><span>+945345223</span></td>
        </tr>
        <tr>
            <td colspan="4">
                <span class="info">لا يسلم الطرد إلا لصاحب الاسم المكتوب على إشعار الشحن</span>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <img src="{{$order->qr_url}}" alt="">
            </td>
            <td>
                <span>الاسم</span>
            </td>
            <td>
                <span>البلدة</span>
            </td>
            <td>
                <span>الأجور</span>
            </td>
        </tr>
        <tr>
            <td></td>
            <td colspan="2"><span>المرسل :</span><span>{{$order->sender?->name}}</span></td>
            <td><span>{{$order->citySource?->name}}</span></td>
            <td>
                @if($order->far>0 && $order->far_sender==false)
                    <span>{{$order->far}} $</span> &nbsp;
                @endif
                @if($order->far_tr>0 && $order->far_sender==false)
                    <span>{{$order->far_tr}} ل.ت</span>
                @endif
            </td>
        </tr>
        <tr>
            <td></td>
            <td colspan="2"><span>المستلم :</span><span>{{$order->receive?->name}}</span></td>
            <td><span>{{$order->cityTarget?->name}}</span></td>
            <td>
                @if($order->far>0 && $order->far_sender)
                    <span>{{$order->far}} $</span> &nbsp;
                @endif
                @if($order->far_tr>0 && $order->far_sender)
                    <span>{{$order->far_tr}} ل.ت</span>
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="5"><span>الرقم : </span><span>{{$order->id}}</span></td>
        </tr>
        <tr>
            <td colspan="5"><span>الوجهة : </span><span>{{$order->receive_address}}</span></td>
        </tr>
        <tr>
            <td colspan="5">
                @if($order->price >0)
                    <span>التحصيل USD : </span>
                    <span>{{$order->price}}</span>
                @endif

                @if($order->price_tr >0)
                    <span>التحصيل تركي : </span>
                    <span>{{$order->price_tr}}</span>
                @endif
            </td>
        </tr>
        <tr>
            <td colspan="5"><span>الشحنة :</span> <span>{{$order->packages->first()?->info}}</span></td>
        </tr>
        <tr>
            <td colspan="5" class="bg-blue">
                <ul>
                    <li>
                        يتعهد المرسل بأن البيانات المسجلة في الإيصال مطابقة للواقع وفي حال مخالفة البيانات يتحمل كامل
                        المسؤولية
                    </li>
                    <li>
                        في حال عدم إستلام المستلم للطلب يلزم المتجر بإرجاع المنتج وتسليم المستحقات المالية في حال قبض
                        الطلب
                    </li>
                </ul>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
