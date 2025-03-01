<?php
session_start();
// PHP Mailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Get form data
$name = $_POST['name'];
$email = $_POST['email'];
$username = $_POST['username'];
$password = $_POST['password'];
$role = $_POST['role'];
$phone_number = $_POST['phone_number'];
$faculty = $_POST['faculty'];

// Store form data in session for later use
$_SESSION['registration_data'] = [
    'name' => $name,
    'email' => $email,
    'username' => $username,
    'password' => $password,
    'role' => $role,
    'phone_number' => $phone_number,
    'faculty' => $faculty
];

// Generate OTP
$otp = rand(100000, 999999);
$_SESSION['otp'] = $otp;

// Setup Gmail SMTP
$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'sdpprojectgroup04@gmail.com';
$mail->Password = 'ksac hwpn pzwd wvwd';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

$mail->setFrom('sdpprojectgroup04@gmail.com', 'Email Verification');
$mail->addAddress($email);

// Set email subject
$mail->Subject = 'Your OTP Code';

// HTML email content
$htmlContent = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Email</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #ffffff;
            color: #333333;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #dddddd;
            border-radius: 8px;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #ff7e5f;
            font-size: 28px;
            font-weight: bold;
            margin: 0;
        }
        .content {
            text-align: center;
        }
        .otp-box {
            display: inline-block;
            margin: 20px 0;
            padding: 15px 20px;
            background-color: #ffffff;
            color: #ff7e5f;
            font-size: 28px;
            font-weight: bold;
            border: 2px solid #ff7e5f;
            border-radius: 8px;
            letter-spacing: 4px;
        }
        .instructions {
            font-size: 16px;
            line-height: 1.6;
            color: #555555;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #999999;
        }
        .footer a {
            color: #ff7e5f;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Your OTP Code</h1>
        </div>
        <div class="content">
            <p class="instructions">Please use the following One-Time Password (OTP) to verify your account:</p>
            <div class="otp-box">' . $otp . '</div> <!-- Dynamic OTP -->
            <p class="instructions">This OTP is valid for the next 5 minutes. Please do not share it with anyone.</p>
        </div>
        <div class="footer">
            <p>If you didn\'t request this code, please ignore this email or <a href="https://wa.me/+94772957834">contact support</a>.</p>
        </div>
    </div>
</body>
</html>
';

// Set email body as HTML
$mail->isHTML(true);
$mail->Body = $htmlContent;

// Send email
if ($mail->send()) {
    echo "success";
} else {
    echo "Error sending OTP: " . $mail->ErrorInfo;
}
?>