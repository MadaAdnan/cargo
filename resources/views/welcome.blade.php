<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <link rel="icon" type="image/png" href={{asset('imgs/img1.png')}}>

    <title>فاتح كارغو </title>

    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            /*background: linear-gradient(to right, #0066ff, #00ccff);*/
            background: linear-gradient(to right, #0066ff, #00ccff),
            fixed;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: white;
        }

        .container {
            text-align: center;
        }

        h1 {
            font-size: 3.5rem;
            animation: fadeIn 3s ease-in-out forwards, slideIn 3s ease-in-out;

            margin-bottom: 50px;
        }

        @keyframes fadeIn {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }

        @keyframes slideIn {
            0% {
                transform: translateY(-100px);
            }
            100% {
                transform: translateY(0);
            }
        }

        .underline {
            display: inline-block;
            margin-top: 10px;
            width: 0;
            height: 3px;
            background: #fff;
            animation: underlineExpand 1s 3s forwards ease-in-out;
        }

        @keyframes underlineExpand {
            0% {
                width: 0;
            }
            100% {
                width: 150px;
            }
        }

        .button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: white;
            color: #0066ff;
            border: none;
            border-radius: 5px;
            font-size: 1.2rem;
            cursor: pointer;
            animation: fadeIn 4s ease-in-out forwards;
            transition: background-color 0.3s ease;
            text-decoration: none;
        }

        .button:hover {
            background-color: #00ccff;
            color: white;
            margin-top: 65px;
        }

        .img {
            height: 250px;
            width: 250px;
            margin: auto;
            object-fit: contain;
        }

        .footer {
            background: linear-gradient(to right, #0066ff, #00ccff);
            color: white;
            text-align: center;
            padding: 20px;
            position: absolute;
            bottom: 0;
            width: 90%;

        }

        .footer a {
    text-decoration: none;
            color:whitesmoke ;
        }

        .footer p {
            margin: 0;
            font-size: 1rem;
            padding: 1px;
        }
    </style>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet"/>

    <!-- Styles -->

</head>
<body class="antialiased">

<div class="container">

    <img class="img" src="{{asset('imgs/img2.png')}}">

    <h1>أهلا وسهلا بكم في شركة الفاتح للشحن</h1>
    <div class="underline"></div>
    <a class="button" href="https://fatihcargo.com/user">استكشف خدماتنا</a>

</div>
<div class="footer">

    <a href="#">
        <p> تطوير شركة مدى للبرمجيات 2024</p>
    </a>

</div>

</body>
</html>
