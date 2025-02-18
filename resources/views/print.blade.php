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
            padding: 3pt;
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
        .logo{
            height: 25mm;
            width: 50mm;
            display: inline-block;
            margin: auto;
        }
        td.img{
            max-width: 75mm;
        }
        @media print {

            *{
                font-size: 11pt;
            }
            ul{

                width: 205mm;

            }
            .printer {
                width: 210mm;
                padding: 3pt;
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


            td{
                max-width: 50mm!important;
            }
            .hide{
                display: none;
            }
            .text-blue{
                color:#1e40af ;
            }
        }
        .hide{
            display: none;
        }
        .text-blue{
            color:#1e40af ;
        }

    </style>
</head>
<body>
<div class="printer">
    <table class="table hide">
        <tr>
            <td rowspan="3" colspan="2" class="w-50"><img style="width: 50mm" src="{{asset('imgs/img1.png')}}" class="logo" alt=""></td>
            <td class="w-50" colspan="2"><span class="text-blue bold">التاريخ :</span> <span>{{$order->shipping_date}}</span></td>
        </tr>
        <tr>
            <td class="w-50" colspan="2">
                <span  class="text-blue bold">للتواصل</span> <span>+945345223</span>
            </td>
        </tr>
        <tr>
            <td class="w-50" colspan="2">
                <span class="text-orange">لا يسلم الطرد إلا لصاحب الاسم المكتوب على إشعار الشحن</span>
            </td>
        </tr>
    </table>
    <table class="table ">
        <tr>
            <td rowspan="3" colspan="2" class="w-50"><img style="width: 50mm" src="{{asset('imgs/img1.png')}}" class="logo" alt=""></td>
            <td class="w-50" colspan="2"><span class="text-blue bold">التاريخ :</span> <span>{{$order->shipping_date}}</span></td>
        </tr>
        <tr>
            <td colspan="2"><span>للتواصل</span></td>
            <td><span>+945345223</span></td>
        </tr>
        <tr>

            <td colspan="4">
                <span class="info">لا يسلم الطرد إلا لصاحب الاسم المكتوب على إشعار الشحن</span>
            </td>
        </tr>
        <tr>
            <td rowspan="3" class="img" >
                <img style="width: 50mm" src="{{asset('imgs/img1.png')}}" alt="">
            </td>
            <td>
                <span>الاسم</span>
            </td>
            <td>
                <span>البلدة</span>
            </td>
            <td colspan="2">
                <span>الأجور</span>
            </td>
        </tr>
        <tr>

            <td ><span>المرسل :</span><span>{{$order->sender?->name}}</span></td>
            <td ><span>{{$order->citySource?->name}}</span></td>
            <td colspan="2">
                @if($order->far>0 && $order->far_sender==false)
                    <span>{{$order->far}} $</span> &nbsp;
                @endif
                @if($order->far_tr>0 && $order->far_sender==false)
                    <span>{{$order->far_tr}} ل.ت</span>
                @endif
            </td>
        </tr>
        <tr>

            <td ><span>المستلم :</span><span>{{$order->receive?->name}}</span></td>
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
<script>
    printDiv()
    function printDiv() {
        var printContents = document.querySelector(".printer").innerHTML;
        var originalContents = document.body.innerHTML;

        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        location.reload(); // لإعادة تحميل الصفحة بعد الطباعة
    }
</script>
</body>
</html>
