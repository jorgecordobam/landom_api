<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document Verified - LandonPro</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #333;">Document Verification Update</h1>
        
        <p>Dear {{ $user->name }},</p>
        
        @if($document->status === 'verified')
            <div style="background-color: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #28a745;">
                <h3 style="margin-top: 0; color: #155724;">✓ Document Verified</h3>
                <p>Your document "<strong>{{ $document->name }}</strong>" has been successfully verified.</p>
            </div>
        @else
            <div style="background-color: #f8d7da; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #dc3545;">
                <h3 style="margin-top: 0; color: #721c24;">✗ Document Rejected</h3>
                <p>Your document "<strong>{{ $document->name }}</strong>" has been rejected.</p>
                @if(isset($rejectionReason))
                    <p><strong>Reason:</strong> {{ $rejectionReason }}</p>
                @endif
            </div>
        @endif
        
        <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #495057;">Document Details:</h3>
            <p><strong>Document:</strong> {{ $document->name }}</p>
            <p><strong>Type:</strong> {{ $document->file_type }}</p>
            <p><strong>Verified by:</strong> {{ $document->verifiedBy->name ?? 'System' }}</p>
            <p><strong>Date:</strong> {{ $document->verified_at->format('F j, Y g:i A') }}</p>
        </div>
        
        @if($document->status === 'rejected')
            <p>Please review the rejection reason and upload a corrected version of your document.</p>
        @else
            <p>Your document is now approved and can be used for your projects and investments.</p>
        @endif
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ config('app.frontend_url') }}/profile/documents" 
               style="background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block;">
                View My Documents
            </a>
        </div>
        
        <p>Best regards,<br>The LandonPro Team</p>
    </div>
</body>
</html>
