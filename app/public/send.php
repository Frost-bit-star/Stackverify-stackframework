<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Send Bulk Email - StackVerify</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background: linear-gradient(135deg, #1e3a8a, #0f172a);
      color: white;
      font-family: 'Segoe UI', sans-serif;
    }
    .glass {
      background: rgba(255, 255, 255, 0.05);
      border-radius: 1rem;
      box-shadow: 0 8px 32px rgba(31, 38, 135, 0.3);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
  </style>
</head>
<body class="bg-auto min-h-screen p-6">
    <header class="bg-white shadow-md p-2 mb-6 flex items-center justify-between border-b border-indigo-500 rounded-full">
        <a href="dashboard.php" class="text-indigo-500 flex items-center">
            <span class="ml-2 font-semibold">Back</span>
        </a>
        <div class="flex items-center">
            <img id="profile-pic" src="https://res.cloudinary.com/dib5bkbsy/image/upload/v1721300995/images_3_prqpng.jpg" alt="Profile Picture" class="rounded-full w-12 h-12">
        </div>
    </header>
<body class="min-h-screen flex items-center justify-center p-6">
  <div class="glass p-6 max-w-2xl w-full">
    <h2 class="text-2xl font-bold mb-4 text-center">📢 Send Bulk Email</h2>
    <form id="emailForm">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="company" class="form-label">Company</label>
          <input type="text" class="form-control" id="company" required>
        </div>
        <div class="col-md-6 mb-3">
          <label for="subject" class="form-label">Subject</label>
          <input type="text" class="form-control" id="subject" required>
        </div>
      </div>
      <div class="mb-3">
        <label for="brand" class="form-label">Brand Name</label>
        <input type="text" class="form-control" id="brand" required>
      </div>
      <div class="mb-3">
        <label for="headline" class="form-label">Headline</label>
        <input type="text" class="form-control" id="headline" required>
      </div>
      <div class="mb-3">
        <label for="subtext" class="form-label">Subtext (optional)</label>
        <input type="text" class="form-control" id="subtext">
      </div>
      <div class="mb-3">
        <label for="message" class="form-label">Main Message</label>
        <textarea class="form-control" id="message" rows="4" required></textarea>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label for="cta" class="form-label">Call to Action (CTA)</label>
          <input type="text" class="form-control" id="cta" required>
        </div>
        <div class="col-md-6 mb-3">
          <label for="footer" class="form-label">Footer (optional)</label>
          <input type="text" class="form-control" id="footer">
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-100">Send Email</button>
    </form>
    <div id="result" class="mt-4 text-sm text-center"></div>
  </div>

  <script>
    const apikey = <?= json_encode($apikey) ?>;
    const form = document.getElementById('emailForm');
    const result = document.getElementById('result');

    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const company = document.getElementById('company').value.trim().toLowerCase();
      const subject = document.getElementById('subject').value.trim();
      const brand = document.getElementById('brand').value.trim();
      const headline = document.getElementById('headline').value.trim();
      const subtext = document.getElementById('subtext').value.trim();
      const message = document.getElementById('message').value.trim();
      const cta = document.getElementById('cta').value.trim();
      const footer = document.getElementById('footer').value.trim();

      try {
        const res = await fetch(`https://rocketverify.onrender.com/api/marketer/${company}/send-email`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'x-api-key': apikey
          },
          body: JSON.stringify({
            subject,
            brand,
            headline,
            subtext,
            message,
            cta,
            footer
          })
        });

        const data = await res.json();
        if (res.ok) {
          result.textContent = `✅ Email sent to ${data.recipients} recipients`;
          result.className = 'mt-4 text-green-400 text-center';
          form.reset();
        } else {
          result.textContent = `❌ ${data.message}`;
          result.className = 'mt-4 text-red-400 text-center';
        }
      } catch (error) {
        result.textContent = '❌ Failed to connect to server.';
        result.className = 'mt-4 text-red-400 text-center';
      }
    });
  </script>
</body>
</html>