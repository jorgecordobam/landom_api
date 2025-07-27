<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>New Investment Alert - LandonPro</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #333;">New Investment Received!</h1>
        
        <p>Dear {{ $user->name }},</p>
        
        <p>Great news! You've received a new investment for your project: <strong>{{ $project->name }}</strong></p>
        
        <div style="background-color: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;">
            <h3 style="margin-top: 0; color: #155724;">Investment Details:</h3>
            <p><strong>Amount:</strong> ${{ number_format($investment->amount, 2) }}</p>
            <p><strong>Investor:</strong> {{ $investment->investor->name }}</p>
            <p><strong>Investment Type:</strong> {{ ucfirst($investment->investment_type) }}</p>
            <p><strong>Date:</strong> {{ $investment->invested_at->format('F j, Y') }}</p>
        </div>
        
        <p>Your project's funding progress:</p>
        <div style="background-color: #f1f3f4; padding: 15px; border-radius: 8px;">
            <p><strong>Current Funding:</strong> ${{ number_format($project->current_funding, 2) }}</p>
            <p><strong>Funding Goal:</strong> ${{ number_format($project->funding_goal, 2) }}</p>
            <p><strong>Progress:</strong> {{ round(($project->current_funding / $project->funding_goal) * 100, 2) }}%</p>
        </div>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ config('app.frontend_url') }}/projects/{{ $project->id }}" 
               style="background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block;">
                View Project Details
            </a>
        </div>
        
        <p>Best regards,<br>The LandonPro Team</p>
    </div>
</body>
</html>
