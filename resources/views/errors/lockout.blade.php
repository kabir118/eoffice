<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>License Activation Required</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --danger-red: #e74c3c;
            --bg-dark: #0f172a;
            --primary-blue: #256f88;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; }
        body {
            background-color: var(--bg-dark);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .lockout-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-top: 4px solid var(--danger-red);
            border-radius: 16px;
            max-width: 420px;
            width: 100%;
            padding: 45px 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
        }
        .lock-icon {
            width: 75px;
            height: 75px;
            background: rgba(231, 76, 60, 0.12);
            border: 2px solid var(--danger-red);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px auto;
            color: var(--danger-red);
            font-size: 2.4rem;
        }
        h1 { font-size: 1.45rem; font-weight: 700; color: #ffffff; margin-bottom: 25px; letter-spacing: -0.3px; }
        .contact-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: linear-gradient(135deg, #256f88 0%, #013756 100%);
            color: #ffffff;
            font-weight: 600;
            font-size: 0.92rem;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(37, 111, 136, 0.3);
            transition: all 0.25s ease;
        }
        .contact-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 111, 136, 0.45);
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="lockout-card">
        <div class="lock-icon">
            <i class="bi bi-shield-lock-fill"></i>
        </div>

        <h1>License Activation Required</h1>

        <a href="mailto:kabirbangash118@gmail.com?subject=License%20Activation%20Request" class="contact-btn">
            <i class="bi bi-envelope-fill"></i>
            <span>Contact for further actions</span>
        </a>
    </div>
</body>
</html>
