async function sendToAI() {
  const userPrompt = document.getElementById('aiInput').value;

  const systemPrompt = `You are Stack's AI Assistant. Help users generate email campaigns, WhatsApp campaigns, draft emails, draft invoices, and anything about digital marketing. Teach users how to do effective digital marketing, copywriting, automation, analytics, and campaign strategy.`;

  const finalPrompt = `${systemPrompt}\n\nUser: ${userPrompt}`;

  const url = 'https://api.dreaded.site/api/chatgpt?text=' + encodeURIComponent(finalPrompt);

  const response = await fetch(url);
  const data = await response.json();

  document.getElementById("responseText").innerText = data.result.prompt || 'No response';
  document.getElementById("aiResponse").style.display = "block";
}

function copyResponse() {
  const text = document.getElementById("responseText").innerText;
  navigator.clipboard.writeText(text);
  alert("Copied to clipboard");
}