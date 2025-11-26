<?php
// gpt_classify.php

function classify_report($title, $content) {
    // ✅ HARD-CODED FOR TESTING – replace with your real key
$apiKey = "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";

    if (!$apiKey || $apiKey === "YOUR_API_KEY_HERE") {
        // If you forgot to replace it, shout loudly
        die("ERROR: API key not set in gpt_classify.php");
    }

    $url = "https://api.openai.com/v1/chat/completions";

    $userPrompt = "
You are a classifier for an internal whistleblower system.

Given a report's title and content, decide:
- severity: one of [Low, Medium, High]
- category: one of [Financial Misconduct, Harassment, Safety Violation, Security Breach, Work Abuse, Other]

Return ONLY JSON in this exact format:
{
  \"severity\": \"Low|Medium|High\",
  \"category\": \"OneCategory\"
}

Title: \"$title\"
Content: \"$content\"
";

    $data = [
        "model" => "gpt-4.1-mini",
        "messages" => [
            [
                "role" => "system",
                "content" => "You are a strict JSON API. Always respond with valid JSON only."
            ],
            [
                "role" => "user",
                "content" => $userPrompt
            ]
        ],
        "temperature" => 0
    ];

    $payload = json_encode($data);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: " . "Bearer " . $apiKey
        ],
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        // If you get SSL errors on Windows, uncomment this line (only for local dev):
        // CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);

    if ($response === false) {
        $err = curl_error($ch);
        curl_close($ch);
        die("cURL error when calling OpenAI: " . $err);
    }

    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status !== 200) {
        // Show full response so we can see the error from OpenAI
        die("OpenAI API returned HTTP $status:\n\n" . $response);
    }

    $result = json_decode($response, true);
    if (!$result || !isset($result["choices"][0]["message"]["content"])) {
        die("Unexpected OpenAI response structure:\n\n" . $response);
    }

    // Assistant content should be a JSON string
    $rawContent = $result["choices"][0]["message"]["content"];

    // For debugging, you can temporarily uncomment:
    // echo "<pre>" . htmlspecialchars($rawContent) . "</pre>"; die();

    $class = json_decode($rawContent, true);
    if (!is_array($class) || !isset($class['severity']) || !isset($class['category'])) {
        die("Assistant did not return valid JSON. Got:\n\n" . $rawContent);
    }

    $severity = $class['severity'];
    $category = $class['category'];

    return [$severity, $category];
}
