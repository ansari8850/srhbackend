<!DOCTYPE html>
<html>
<head>
    <title>Password Reset</title>
</head>
<body>
    <p>You are receiving this email because we received a password reset request for your account.</p>
    <p>Please click the link below to reset your password:</p>
    <a href="{{ url('reset-password/' . $token) }}">Reset Password</a>
    <p>If you did not request a password reset, no further action is required.</p>
</body>
</html>
