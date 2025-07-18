<?php
/**
 * ai.php
 * Stack Framework AI Helper
 * 
 * Example usage:
 *   $reply = fetchStackVerifyAI($userId, $userMessage);
 */

function fetchStackVerifyAI($userId, $userMessage) {
    $flirtyFallback = "🥺 Hang on… my brain is having a cute jam 🧠✨ 💖";

    if (!$userId || !is_string($userId) || !$userMessage || !is_string($userMessage)) {
        return $flirtyFallback;
    }

    // Conversation memory can be implemented via DB or cache for persistence
    // Here we use a static variable for simplicity within a single request
    static $conversationMemory = [];

    if (!isset($conversationMemory[$userId])) {
        $conversationMemory[$userId] = [];
    }

    // Add user message to history
    $conversationMemory[$userId][] = "User: $userMessage";

    // Trim to last 5 messages
    if (count($conversationMemory[$userId]) > 5) {
        $conversationMemory[$userId] = array_slice($conversationMemory[$userId], -5);
    }

    // SYSTEM PROMPT
    $systemPrompt = "
You are Stack's AI Assistant. Help users generate email campaigns, WhatsApp campaigns, draft emails, draft invoices, and anything about digital marketing. Teach users how to do effective digital marketing, copywriting, automation, analytics, and campaign strategy.
";

    // Compose prompt
    $combinedText = "Conversation history:\n" . implode("\n", $conversationMemory[$userId]) . "\n\nInstructions:\n" . $systemPrompt;

    // Check length (practical ~2000 chars)
    if (strlen($combinedText) > 1800) {
        // Trim to last 3 messages if too long
        $conversationMemory[$userId] = array_slice($conversationMemory[$userId], -3);
        $combinedText = "Conversation history:\n" . implode("\n", $conversationMemory[$userId]) . "\n\nInstructions:\n" . $systemPrompt;
    }

    $apiUrl = 'https://api.dreaded.site/api/chatgpt?text=' . urlencode($combinedText);

    // Perform GET request
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        return $flirtyFallback;
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status !== 200) {
        return $flirtyFallback;
    }

    $data = json_decode($result, true);
    $aiReply = $data['result']['prompt'] ?? null;

    if ($aiReply && is_string($aiReply)) {
        // Add AI reply to history
        $conversationMemory[$userId][] = "Me: " . trim($aiReply);

        // Trim to last 5 messages
        if (count($conversationMemory[$userId]) > 5) {
            $conversationMemory[$userId] = array_slice($conversationMemory[$userId], -5);
        }

        return trim($aiReply);
    }

    return $flirtyFallback;
}
