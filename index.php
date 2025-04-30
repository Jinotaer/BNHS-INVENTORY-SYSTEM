<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" sizes="32x32" href="bnhs1.png">
    <title>Bukidnon National High School Inventory System</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,400,600" rel="stylesheet">

    <!-- Styles -->
    <style>
        /* #636b6f */
        html,
        body {
            background: linear-gradient(to bottom right, #d9f0ff, #ffffff);
            color: #1e1e1e;
            font-family: 'Nunito', sans-serif;
            font-weight: 400;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }

        .content {
            text-align: center;
        }

        .logo-img {
            margin-bottom: 20px;
        }

        .title {
            font-family: 'verdana', sans-serif;
            font-size: 74px;
            padding: 20px;
        }

        .links>a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }


        .buttons a {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px;
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            text-transform: uppercase;
        }

        .buttons a.admin {
            background-color: #29126b;
            /* Blue */
        }

        .buttons a.cashier {
            background-color: #29126d;
            /* Red */
        }

        .buttons a.customer {
            background-color: #29126d;
            /* Green */
        }

        .buttons a:hover {
            opacity: 0.5;
        }

        .logo {
            margin: 0%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .logo img {
            width: 350px;
            height: auto;
        }

        .logo div {
            margin-top: 5px;
        }
    </style>
    <style>
        @media (max-width: 768px) {
            .title {
                font-size: 48px;
            }

            .buttons a {
                padding: 12px 16px;
                font-size: 14px;
                display: grid;
                margin: 20px;
            }

            .logo img {
                width: 250px;
                /* Adjust the width for smaller screens */
            }
        }

        @media (max-width: 480px) {
            .title {
                font-size: 36px;
            }

            .buttons a {
                display: grid;
                padding: 10px 12px;
                font-size: 12px;
                margin: 20px;
            }

            .logo img {
                width: 200px;
                /* Adjust the width for smaller screens */
            }
        }
    </style>
</head>

<body>
    <div class="flex-center position-ref full-height">
        <div class="content">
            <div class="title m-b-md">
                <div class="logo">
                    <img src="bnhs1.png" alt="POS Logo" class="logo-img">
                    <div>BNHS INVENTORY SYSTEM</div>
                </div>
            </div>
            <div class="buttons">
                <a href="BNHS/admin/" class="admin">Admin Log In</a>
                <a href="BNHS/staff/" class="cashier">Staff Log In</a>
                <!-- <a href="Main/customer/" class="customer">Customer Log In</a> -->
            </div>
        </div>
    </div>
</body>

<script src="https://www.google.com/recaptcha/api.js"></script>
</html>