<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport"
        content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, shrink-to-fit=no" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        * {
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Montserrat', sans-serif;
            background: #f1f1f1;
        }
    </style>
</head>

<body>
    <table align="center" style="width: 600px; background: #fff;border-collapse: collapse;">
        <tr>
            <td>
                <table style="text-align: center; background: #fff;padding: 0 25px 30px; width: 100%;">
                    <tr>
                        <td>
                            <a href="#" target="_blank">
                                <img style="padding: 15px 0;width: 100%; height: 80px; object-fit: contain;"
                                    src="#" alt="logo-image">
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            {{-- <h4 style="margin:20px 0 8px 0;color: #000;font-size: 22px;font-weight: 700;">OTP Verification</h4> --}}
                            <h4 style="margin:20px 0 8px 0;color: #000;font-size: 22px;font-weight: 700;">{{ $otp }}</h4>

                            <h6 style="margin-top: 0;font-size: 12px;font-weight: 500;">
                                OTP for HRMS ERP is {{ $otp }}. This OTP is valid for the next 10 minutes. - Team HRMS ERP
                            </h6>
                        </td>
                    </tr>
                    {{-- <tr>
                        <td>
                            <a href="#"
                                style="font-size: 12px;font-weight: 600;background: #768d17;color: #fff;padding: 10px 20px;border-radius: 4px; text-decoration: none;display: inline-block;min-width: 150px;">View or Manage Order</a>
                        </td>
                    </tr> --}}
                    <tr>
                        <td>
                            <h6
                                style=" margin-bottom: 10px;margin-top: 35px;font-size: 16px; font-weight: 600; color: #171c2c;">
                                Need Help?</h6>
                            <p style="margin: 0;font-size: 12px;font-weight: 500; color: #a6a6a6;">Please send us
                                feedback or email to<br>
                                <a href="mailto:support@fiveferns.com"
                                    style="margin: 0;font-size: 12px;font-weight: 500; color: #768d17;text-decoration: none;">support@hrmserp.com</a>
                            </p>
                        </td>
                    </tr>
                </table>
                <table style="background: #f9f7fa;text-align: center; width: 100%; padding: 20px 0;">
                    <tr>
                        <td>
                            <h6 style="margin: 0 0 10px 0; color: #a6a6a6;font-size: 10px; font-weight: 500;">&copy;
                                HRMS ERP. All Rights Reserved.</h6>
                            {{-- <a href="#"
                                style="margin: 5px;display: inline-block;width: 35px;height: 35px;border-radius:50%;background: #fff;padding:8px 0;box-sizing: border-box;">
                                <img src="{{ asset('images/fb.png') }}" alt="">
                            </a>
                            <a href="#"
                                style="margin: 5px;display: inline-block;width: 35px;height: 35px;border-radius:50%;background: #fff;padding:8px 0;box-sizing: border-box;">
                                <img src="{{ asset('images/twitter.png') }}" alt="">
                            </a>
                            <a href="#"
                                style="margin: 5px;display: inline-block;width: 35px;height: 35px;border-radius:50%;background: #fff;padding:8px 0;box-sizing: border-box;">
                                <img src="{{ asset('images/linkedin.png') }}" alt="">
                            </a> --}}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
