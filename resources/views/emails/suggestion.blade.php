<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suggestion / Issue — USTB e-Office</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; margin: 0; padding: 0; color: #1a1a1a; }
        .wrapper { max-width: 620px; margin: 30px auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #013756 0%, #025a8a 100%); padding: 28px 32px; }
        .header-top { display: flex; align-items: center; gap: 14px; }
        .header-logo { width: 52px; height: 52px; object-fit: contain; }
        .header-title { color: #ffffff; }
        .header-title h1 { margin: 0; font-size: 1.1rem; font-weight: 700; line-height: 1.2; }
        .header-title span { font-size: 0.72rem; color: #dba036; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }
        .tag { display: inline-block; margin-top: 14px; background: rgba(255,255,255,0.15); color: #fff; font-size: 0.75rem; font-weight: 600; padding: 4px 12px; border-radius: 20px; letter-spacing: 0.5px; }
        .body { padding: 32px; }
        .subject-line { font-size: 1.15rem; font-weight: 700; color: #013756; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 2px solid #e8ecf0; }
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; font-size: 0.85rem; }
        .meta-table td { padding: 8px 12px; border: 1px solid #e2e8f0; }
        .meta-table td:first-child { background-color: #f6f8fa; font-weight: 700; color: #013756; width: 130px; }
        .message-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #013756; border-radius: 6px; padding: 18px 20px; font-size: 0.92rem; line-height: 1.7; color: #2c3e50; }
        .message-box p { margin: 0 0 10px 0; }
        .message-box p:last-child { margin-bottom: 0; }
        .message-box img { max-width: 100%; height: auto; border-radius: 6px; margin: 10px 0; display: block; }
        .message-box ul, .message-box ol { margin: 0 0 10px 0; padding-left: 20px; }
        .message-box blockquote { border-left: 3px solid #cbd5e1; margin: 10px 0; padding-left: 12px; color: #64748b; font-style: italic; }
        .message-box pre { background: #e2e8f0; padding: 10px; border-radius: 4px; overflow-x: auto; font-family: monospace; }
        .footer { background-color: #f4f6f9; padding: 18px 32px; text-align: center; border-top: 1px solid #e2e8f0; }
        .footer p { margin: 0; font-size: 0.72rem; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <img src="https://ustb.edu.pk/assets/img/logo.png" alt="UST Bannu Logo" class="header-logo">
                <div class="header-title">
                    <h1>University of Science & Technology, Bannu</h1>
                    <span>e-Office System</span>
                </div>
            </div>
            <div class="tag">📬 New Suggestion / Issue Received</div>
        </div>

        <!-- Body -->
        <div class="body">
            <div class="subject-line">{{ $subject }}</div>

            <table class="meta-table">
                <tr>
                    <td>From</td>
                    <td>{{ $senderName }}</td>
                </tr>
                <tr>
                    <td>Email</td>
                    <td>{{ $senderEmail }}</td>
                </tr>
                @if($senderDesignation)
                <tr>
                    <td>Designation</td>
                    <td>{{ $senderDesignation }}</td>
                </tr>
                @endif
                @if($senderDepartment)
                <tr>
                    <td>Department</td>
                    <td>{{ $senderDepartment }}</td>
                </tr>
                @endif
                <tr>
                    <td>Date &amp; Time</td>
                    <td>{{ now()->format('D, d M Y — h:i A') }}</td>
                </tr>
            </table>

            <div class="message-box">{!! $messageBody !!}</div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This message was submitted via the USTB e-Office Suggestion &amp; Issues form.</p>
            <p style="margin-top: 4px;">You can reply directly to this email to respond to {{ $senderName }}.</p>
        </div>
    </div>
</body>
</html>
