<?php
require_once __DIR__ . '/../connection/init.php';

// Create log table if not exists
try {
    $db->exec("CREATE TABLE IF NOT EXISTS whatsapp_sends (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        message TEXT,
        recipients INTEGER,
        sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}

// Helper to normalize Kenyan phone numbers to 254XXXXXXXXX format without plus
function normalizePhoneNumber($number) {
    $digits = preg_replace('/\D+/', '', $number);
    if (substr($digits, 0, 1) === '0' && strlen($digits) === 10) {
        return '254' . substr($digits, 1);
    } elseif (substr($digits, 0, 3) === '254' && strlen($digits) === 12) {
        return $digits;
    } elseif (strlen($digits) === 9 && substr($digits, 0, 1) === '7') {
        return '254' . $digits;
    }
    return $digits;
}

// Initialize status message
$status = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $apiKey = trim($_POST['apiKey'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!$apiKey || !$message) {
        $status = "❌ API Key and message are required.";
    } else {
        // Fetch contacts from DB
        $stmt = $db->query("SELECT phone FROM contacts WHERE phone IS NOT NULL AND phone != ''");
        $rawContacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Normalize numbers
        $contacts = [];
        foreach ($rawContacts as $row) {
            $normalized = normalizePhoneNumber($row['phone']);
            if (strlen($normalized) === 12 && substr($normalized, 0, 3) === '254') {
                $contacts[] = $normalized;
            }
        }

        if (count($contacts) === 0) {
            $status = "❌ No valid contacts found.";
        } else {
            // Send bulk message
            $payload = [
                'numbers' => $contacts,
                'template' => $message,
            ];

            $opts = [
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/json\r\nx-api-key: $apiKey\r\n",
                    'content' => json_encode($payload),
                    'timeout' => 30,
                ]
            ];

            $context = stream_context_create($opts);
            $response = @file_get_contents('https://rocketwhatsapp.onrender.com/bulk-message', false, $context);
            $result = json_decode($response, true);

            if ($result && isset($result['results'])) {
                $sentCount = 0;
                foreach ($result['results'] as $r) {
                    if ($r['status'] === 'sent') {
                        $sentCount++;
                    }
                }

                $status = "✅ Bulk message sent successfully to $sentCount contacts.";

                // Log
                $logStmt = $db->prepare("INSERT INTO whatsapp_sends (message, recipients) VALUES (?, ?)");
                $logStmt->execute([$message, $sentCount]);
            } else {
                $status = "❌ Failed to send bulk message. Response: " . htmlspecialchars($response);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Bulk WhatsApp - StackVerify</title>
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
      max-width: 600px;
      margin: auto;
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

<?php require __DIR__ . '/header.php'; ?>

<div class="content">
  <h3>Send Bulk WhatsApp Message</h3>
  <form method="POST" action="">
    <input type="text" name="apiKey" placeholder="API Key (required)" required value="<?php echo htmlspecialchars($_POST['apiKey'] ?? ''); ?>">
    <textarea name="message" placeholder="Type your message" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
    <button class="send-btn" type="submit">Send to All Contacts</button>
  </form>
  <div class="status"><?php echo htmlspecialchars($status); ?></div>
</div>

<?php require __DIR__ . '/footer.php'; ?>

<script>
  function toggleTheme() {
    document.body.classList.toggle('dark');
  }
</script>

</body>
</html>