<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to LandonPro</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #333;">Welcome to LandonPro!</h1>
        
        <p>Dear {{ $user->name }},</p>
        
        <p>Welcome to LandonPro, the premier platform for construction project investment and management.</p>
        
        <p>Your account has been successfully created. You can now:</p>
        <ul>
            <li>Explore investment opportunities</li>
            <li>Manage your projects</li>
            <li>Connect with other professionals</li>
        </ul>
        
        <p>If you have any questions, please don't hesitate to contact our support team.</p>
        
        <p>Best regards,<br>The LandonPro Team</p>
    </div>
</body>
</html>
