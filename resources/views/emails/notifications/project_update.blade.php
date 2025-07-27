<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Project Update - LandonPro</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #333;">Project Update</h1>
        
        <p>Dear {{ $user->name }},</p>
        
        <p>There's been an update on the project: <strong>{{ $project->name }}</strong></p>
        
        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #495057;">Update Details:</h3>
            <p><strong>Progress:</strong> {{ $project->progress_percentage }}%</p>
            <p><strong>Status:</strong> {{ ucfirst($project->status) }}</p>
            @if(isset($updateMessage))
                <p><strong>Message:</strong> {{ $updateMessage }}</p>
            @endif
        </div>
        
        <p>You can view more details about this project by logging into your LandonPro account.</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ config('app.frontend_url') }}/projects/{{ $project->id }}" 
               style="background-color: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block;">
                View Project
            </a>
        </div>
        
        <p>Best regards,<br>The LandonPro Team</p>
    </div>
</body>
</html>
