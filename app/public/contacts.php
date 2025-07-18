<?php
require_once __DIR__ . '/header.php'; // correct relative path

// Handle form submission
$status = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['userName'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';

    if ($name && $phone) {
        try {
            $db = new PDO('sqlite:' . __DIR__ . '/../db/app.sqlite');
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create contacts table if it doesn't exist
            $db->exec("CREATE TABLE IF NOT EXISTS contacts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                phone TEXT NOT NULL,
                email TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");

            // Insert contact
            $stmt = $db->prepare("INSERT INTO contacts (name, phone, email) VALUES (?, ?, ?)");
            $stmt->execute([$name, $phone, $email]);

            $status = "Contact saved successfully!";
        } catch (PDOException $e) {
            $status = "DB Error: " . $e->getMessage();
        }
    } else {
        $status = "Name and Phone are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Save Contact - StackVerify</title>
  <style>
    /* Your CSS unchanged for layout consistency */
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
    body { margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; background: var(--bg-color); color: var(--text-color); display: flex; flex-direction: column; height: 100vh; transition: background 0.3s, color 0.3s; }
    .header { background: var(--card-bg); backdrop-filter: blur(10px); display: flex; justify-content: space-between; align-items: center; padding: 10px 15px; box-shadow: 0 2px 10px var(--card-shadow); }
    .header .logo-section { display: flex; align-items: center; }
    .header img.logo { width: 35px; height: 35px; margin-right: 10px; }
    .header .app-name { font-weight: bold; font-size: 1.1em; }
    .header .right-section { display: flex; align-items: center; }
    .header img.profile { width: 35px; height: 35px; border-radius: 50%; margin-left: 10px; }
    .theme-toggle { background: var(--button-bg); color: var(--button-text); border: none; border-radius: 8px; padding: 6px 10px; cursor: pointer; }
    .content { flex: 1; overflow-y: auto; padding: 20px 15px 70px; display: flex; flex-direction: column; justify-content: flex-start; }
    .content h3 { text-align: center; margin-bottom: 20px; }
    .content input { width: 100%; padding: 14px; margin: 10px 0; border-radius: 10px; border: 1px solid #ccc; font-size: 1em; }
    .save-btn { width: 100%; background: var(--button-bg); color: var(--button-text); border: none; border-radius: 10px; padding: 16px; font-size: 1em; font-weight: bold; cursor: pointer; margin-top: 10px; }
    .status { margin-top: 10px; text-align: center; font-size: 0.9em; }
    .bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; background: var(--card-bg); backdrop-filter: blur(10px); display: flex; justify-content: space-around; padding: 10px 0; border-top: 1px solid #ddd; box-shadow: 0 -2px 10px var(--card-shadow); }
    .bottom-nav button { background: none; border: none; font-size: 0.8em; display: flex; flex-direction: column; align-items: center; color: var(--text-color); cursor: pointer; }
    .bottom-nav button img { width: 25px; height: 25px; margin-bottom: 3px; filter: var(--nav-icon-filter); }
  </style>
</head>
<body>
  <div class="content">
    <h3>Save Contact</h3>
    <form method="POST" action="index.php?route=contacts">
      <input type="text" name="userName" placeholder="User Name (required)" required>
      <input type="text" name="phone" placeholder="Phone Number (required)" required>
      <input type="email" name="email" placeholder="Email (optional)">
      <button class="save-btn" type="submit">Save Contact</button>
    </form>
    <div class="status"><?php echo htmlspecialchars($status); ?></div>
  </div>

  <?php require_once __DIR__ . '/footer.php'; ?>

  <script>
    function toggleTheme() {
      document.body.classList.toggle('dark');
    }
  </script>
</body>
</html>