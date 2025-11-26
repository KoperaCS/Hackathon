<?php
$OPENAI_API_KEY = "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";

if (!$OPENAI_API_KEY) {
    die("API key missing. Set OPENAI_API_KEY environment variable.");
}

$url = "https://api.openai.com/v1/chat/completions";

$data = [
    "model" => "gpt-4.1-mini",
    "messages" => [
        ["role" => "user", "content" => $_POST['prompt'] ?? "Hello!"]
    ]
];

$payload = json_encode($data);

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer $OPENAI_API_KEY"
    ],
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_RETURNTRANSFER => true
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo "Request error: " . curl_error($ch);
    exit;
}

curl_close($ch);

// Decode JSON response
$result = json_decode($response, true);

// Print the assistant's reply
echo "<h2>Response:</h2>";
echo "<pre>";
echo $result["choices"][0]["message"]["content"] ?? "No response";
echo "</pre>";
