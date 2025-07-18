<?php
require_once __DIR__ . '/../logic/Ai.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Stack Dashboard</title>
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

    .header img.profile {
      width: 35px;
      height: 35px;
      border-radius: 50%;
    }

    .theme-toggle {
      margin-left: 10px;
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
      padding: 0 15px 70px;
    }

    .card-button {
      background: none;
      border: none;
      width: 100%;
      text-align: left;
      padding: 0;
      margin: 0 0 15px;
      cursor: pointer;
    }

    .card {
      background: var(--card-bg);
      backdrop-filter: blur(10px);
      border-radius: 20px;
      box-shadow: 0 10px 20px var(--card-shadow);
      padding: 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      transition: transform 0.2s;
    }

    .card:hover {
      transform: translateY(-3px);
    }

    .card img {
      width: 40px;
      height: 40px;
      margin-right: 15px;
      filter: var(--nav-icon-filter);
    }

    .card .info h3 {
      margin: 0;
      font-size: 1em;
    }

    .card .info p {
      margin: 5px 0 0;
      font-size: 0.8em;
      color: #666;
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

    .ai-section {
      padding: 20px;
    }

    .ai-box {
      background: var(--card-bg);
      backdrop-filter: blur(10px);
      border-radius: 15px;
      box-shadow: 0 2px 8px var(--card-shadow);
      display: flex;
      align-items: center;
      padding: 10px;
    }

    .ai-box textarea {
      flex: 1;
      border: none;
      resize: none;
      padding: 10px;
      font-size: 14px;
      outline: none;
      border-radius: 10px;
      background: transparent;
      color: var(--text-color);
    }

    .send-btn {
      background: var(--button-bg);
      color: var(--button-text);
      border: none;
      border-radius: 8px;
      padding: 10px 16px;
      margin-left: 10px;
      font-size: 14px;
      cursor: pointer;
    }

    .ai-options {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 10px;
    }

    .ai-options button {
      background: #f2f4ff;
      border: none;
      padding: 8px 12px;
      border-radius: 20px;
      font-size: 13px;
      cursor: pointer;
    }

    body.dark .ai-options button {
      background: #333;
      color: white;
    }

    .ai-response {
      background: var(--card-bg);
      backdrop-filter: blur(10px);
      margin-top: 20px;
      padding: 15px;
      border-radius: 12px;
      position: relative;
      box-shadow: 0 1px 4px var(--card-shadow);
    }

    .response-text {
      font-size: 14px;
      white-space: pre-wrap;
    }

    .copy-btn {
      position: absolute;
      top: 10px;
      right: 10px;
      background: #e0e0e0;
      border: none;
      border-radius: 8px;
      padding: 5px 10px;
      font-size: 12px;
      cursor: pointer;
    }
  </style>
</head>
<body>

<?php require_once 'header.php'; ?>

<div class="ai-section">
  <form method="post">
    <div class="ai-box">
      <textarea name="aiInput" placeholder="Describe your idea"><?php echo isset($_POST['aiInput']) ? htmlspecialchars($_POST['aiInput']) : ''; ?></textarea>
      <button type="submit" class="send-btn">Send</button>
    </div>
  </form>

  <div class="ai-options">
    <button type="button" onclick="pasteOption('Design for me')">Design for me</button>
    <button type="button" onclick="pasteOption('Create an image')">Create an image</button>
    <button type="button" onclick="pasteOption('Draft a doc')">Draft a doc</button>
    <button type="button" onclick="pasteOption('Create for me email')">Create for me email</button>
    <button type="button" onclick="pasteOption('Create a video clip')">Create a video clip</button>
  </div>

  <?php
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['aiInput'])) {
    $userId = 'test-user'; // Replace with dynamic user ID
    $userMessage = trim($_POST['aiInput']);
    $aiReply = fetchStackVerifyAI($userId, $userMessage);
    ?>
    <div id="aiResponse" class="ai-response">
      <div class="response-text"><?php echo htmlspecialchars($aiReply); ?></div>
      <button onclick="copyResponse()" class="copy-btn">Copy</button>
    </div>
    <?php
  }
  ?>
</div>

<div class="content">
  <button class="card-button" onclick="location.href='index.php?route=bulk-email'">
    <div class="card">
      <img src="https://img.icons8.com/color/48/000000/new-post.png"/>
      <div class="info">
        <h3>Bulk and Single Email Marketing</h3>
        <p>Send personalised or mass emails easily.</p>
      </div>
    </div>
  </button>

  <button class="card-button" onclick="location.href='index.php?route=bulk-message'">
    <div class="card">
      <img src="https://img.icons8.com/color/48/000000/whatsapp.png"/>
      <div class="info">
        <h3>Bulk and Single WhatsApp Messaging</h3>
        <p>Broadcast and direct messages via WhatsApp.</p>
      </div>
    </div>
  </button>

  <button class="card-button" onclick="location.href='index.php?route=social-posting'">
    <div class="card">
      <img src="https://img.icons8.com/color/48/000000/facebook-new.png"/>
      <div class="info">
        <h3>Automated Social Posting</h3>
        <p>Post to Facebook, Instagram, TikTok easily.</p>
      </div>
    </div>
  </button>

  <button class="card-button" onclick="location.href='index.php?route=contacts'">
    <div class="card">
      <img src="https://img.icons8.com/color/48/000000/instagram-new.png"/>
      <div class="info">
        <h3>Add Contacts</h3>
        <p>Track and add users contacts.</p>
      </div>
    </div>
  </button>
</div>

<?php require_once 'footer.php'; ?>

<script>
  function toggleTheme() {
    document.body.classList.toggle('dark');
  }

  function pasteOption(text) {
    document.querySelector("textarea[name='aiInput']").value = text;
  }

  function copyResponse() {
    const text = document.querySelector(".response-text").innerText;
    navigator.clipboard.writeText(text);
    alert("Copied AI response!");
  }
</script>

</body>
</html>