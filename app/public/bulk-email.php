<?php
require_once __DIR__ . '/../connection/init.php';
require_once 'header.php';

// Load email API config from .env via init.php
$emailApiUrl = $config['EMAIL_API_URL'] ?? '';
$emailApiKey = $config['EMAIL_API_KEY'] ?? '';

// Ensure sent_emails table exists
try {
    $db->exec("CREATE TABLE IF NOT EXISTS sent_emails (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        contact_name TEXT,
        contact_email TEXT,
        subject TEXT,
        message TEXT,
        sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Fetch contacts with emails
    $stmt = $db->query("SELECT name, email FROM contacts WHERE email IS NOT NULL AND email != ''");
    $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

// Handle bulk send submission
$status = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_send'])) {
    $company = trim($_POST['companyName'] ?? '');
    $subject = trim($_POST['subject'] ?? 'Notification');
    $bodyText = trim($_POST['body'] ?? '');
    $linkText = trim($_POST['linkText'] ?? '');
    $linkUrl = trim($_POST['linkUrl'] ?? '');

    if (!$company || !$bodyText) {
        $status = "❌ Company Name and Message body are required.";
    } elseif (count($contacts) === 0) {
        $status = "❌ No contacts with email found.";
    } else {
        $successCount = 0;
        $failCount = 0;

        // Prepare email body with optional link
        $finalMessage = $bodyText . "\n" . ($linkText && $linkUrl ? "$linkText: $linkUrl\n" : "") . "— $company";

        foreach ($contacts as $c) {
            $payload = [
                'to' => $c['email'],
                'subject' => $subject,
                'company_name' => $company,
                'body' => $finalMessage
            ];

            $options = [
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/json\r\nx-api-key: $emailApiKey\r\n",
                    'content' => json_encode($payload),
                    'timeout' => 30
                ]
            ];

            $context = stream_context_create($options);
            $response = @file_get_contents($emailApiUrl, false, $context);

            if ($response === FALSE) {
                $failCount++;
                error_log("Error sending to {$c['email']}: " . json_encode(error_get_last()));
            } else {
                $result = json_decode($response, true);
                if (isset($result['message']) && strpos($result['message'], 'sent') !== false) {
                    $successCount++;

                    // Log each sent email
                    $stmt = $db->prepare("INSERT INTO sent_emails (contact_name, contact_email, subject, message) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$c['name'], $c['email'], $subject, $finalMessage]);
                } else {
                    $failCount++;
                    error_log("API fail for {$c['email']}: " . $response);
                }
            }
        }

        $status = "✅ Sent: $successCount, Failed: $failCount";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Bulk Emails - StackVerify</title>
  <style>
    :root {
      --bg-color: #f6f8fb;
      --text-color: #333;
      --card-bg: rgba(255, 255, 255, 0.8);
      --card-shadow: rgba(0, 0, 0, 0.1);
      --button-bg: #4c8bf5;
      --button-text: white;
      --nav-icon-filter: none;
    }

    body.dark {
      --bg-color: #121212;
      --text-color: #f0f0f0;
      --card-bg: rgba(18, 18, 18, 0.8);
      --card-shadow: rgba(0, 0, 0, 0.6);
      --button-bg: #6a5acd;
      --button-text: white;
      --nav-icon-filter: invert(1);
    }

    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', sans-serif;
      background: var(--bg-color);
      color: var(--text-color);
      display: flex;
      flex-direction: column;
      height: 100vh;
      transition: background 0.3s, color 0.3s;
    }

    .header {
      background: var(--card-bg);
      backdrop-filter: blur(10px);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 15px;
      box-shadow: 0 2px 10px var(--card-shadow);
    }

    .header .logo-section {
      display: flex;
      align-items: center;
    }

    .header img.logo {
      width: 35px;
      height: 35px;
      margin-right: 10px;
    }

    .header .app-name {
      font-weight: bold;
      font-size: 1.1em;
    }

    .header .right-section {
      display: flex;
      align-items: center;
    }

    .header img.profile {
      width: 35px;
      height: 35px;
      border-radius: 50%;
      margin-left: 10px;
    }

    .theme-toggle {
      background: var(--button-bg);
      color: var(--button-text);
      border: none;
      border-radius: 8px;
      padding: 6px 10px;
      cursor: pointer;
    }

    .content {
      flex: 1;
      overflow-y: auto;
      padding: 20px 15px 70px;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
    }

    .content h3 {
      text-align: center;
      margin-bottom: 20px;
    }

    .content input, .content textarea {
      width: 100%;
      padding: 14px;
      margin: 10px 0;
      border-radius: 10px;
      border: 1px solid #ccc;
      font-size: 1em;
    }

    .send-btn {
      width: 100%;
      background: var(--button-bg);
      color: var(--button-text);
      border: none;
      border-radius: 10px;
      padding: 16px;
      font-size: 1em;
      font-weight: bold;
      cursor: pointer;
      margin-top: 10px;
    }

    .status {
      margin-top: 10px;
      text-align: center;
      font-size: 0.9em;
    }

    .bottom-nav {
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: var(--card-bg);
      backdrop-filter: blur(10px);
      display: flex;
      justify-content: space-around;
      padding: 10px 0;
      border-top: 1px solid #ddd;
      box-shadow: 0 -2px 10px var(--card-shadow);
    }

    .bottom-nav button {
      background: none;
      border: none;
      font-size: 0.8em;
      display: flex;
      flex-direction: column;
      align-items: center;
      color: var(--text-color);
      cursor: pointer;
    }

    .bottom-nav button img {
      width: 25px;
      height: 25px;
      margin-bottom: 3px;
      filter: var(--nav-icon-filter);
    }
  </style>
</head>
<body>
  <div class="content">
    <h3>Send Bulk Email</h3>
    <form method="POST" action="">
      <input type="text" name="companyName" placeholder="Company Name (required)" required>
      <input type="text" name="subject" placeholder="Email Subject (optional)">
      <textarea name="body" placeholder="Type your email message here" required></textarea>
      <input type="text" name="linkText" placeholder="Link Text (optional)">
      <input type="url" name="linkUrl" placeholder="Link URL (optional)">
      <button class="send-btn" type="submit" name="bulk_send">Send to All Contacts</button>
    </form>
    <div class="status"><?php echo htmlspecialchars($status); ?></div>
  </div>

  <script>
    function toggleTheme() {
      document.body.classList.toggle('dark');
    }
  </script>
  
<?php require_once 'footer.php'; ?>
</body>
</html>