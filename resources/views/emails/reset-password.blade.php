<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إعادة تعيين كلمة المرور</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin:0;
            padding:0;
            font-family:'Tahoma', Arial, sans-serif;
            background-color:#f4f4f8; /* خلفية فاتحة أنيقة */
            direction:rtl;
        }
        .container {
            max-width:600px;
            margin:30px auto;
            background:#ffffff;
            border-radius:16px;
            overflow:hidden;
            box-shadow:0 6px 20px rgba(0,0,0,0.08);
            padding:30px;
            color:#2d2d2d;
            line-height:1.8;
        }
        .header {
            text-align:center;
            background:linear-gradient(135deg, #8b5cf6, #6d28d9);
            color:#fff;
            padding:22px;
            font-size:22px;
            font-weight:bold;
            border-radius:12px 12px 0 0;
        }
        .btn {
            display:inline-block;
            padding:14px 28px;
            margin:30px 0;
            background:linear-gradient(135deg, #8b5cf6, #6d28d9);
            color:#fff !important;
            text-decoration:none;
            border-radius:50px;
            font-size:16px;
            font-weight:bold;
            transition:opacity 0.3s ease;
            box-shadow:0 3px 10px rgba(109,40,217,0.3);
        }
        .btn:hover {
            opacity:0.9;
        }
        .footer {
            text-align:center;
            color:#666;
            font-size:12px;
            margin-top:30px;
            border-top:1px solid #eee;
            padding-top:15px;
        }

        @media only screen and (max-width:620px) {
            .container {
                width:95% !important;
                padding:20px !important;
            }
            .btn {
                display:block !important;
                width:100% !important;
                text-align:center !important;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">🔒 إعادة تعيين كلمة المرور</div>

        <p>مرحباً <strong>{{ $user->name }}</strong>،</p>
        <p>لقد استلمنا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك على <strong>QREGY</strong>.</p>
        <p>اضغط على الزر بالأسفل للمتابعة:</p>

        <p style="text-align:center;">
            <a href="{{ $url }}" class="btn">إعادة تعيين كلمة المرور</a>
        </p>

        <p style="font-size:14px; color:#777;">
            إذا لم تطلب إعادة تعيين كلمة المرور، يمكنك تجاهل هذه الرسالة بأمان.
        </p>

        <div class="footer">
            &copy; {{ date('Y') }} <strong>QREGY</strong> — جميع الحقوق محفوظة.
        </div>
    </div>

</body>
</html>
