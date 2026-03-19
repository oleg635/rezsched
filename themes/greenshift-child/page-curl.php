<?php

// Initialize cURL
$ch = curl_init();

// URL you want to request
$url = "https://example.com/api/endpoint";
$username = "demo";
$password = "access";

// Set the URL
curl_setopt($ch, CURLOPT_URL, $url);

// Enable basic HTTP authentication with username and password
curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");

// Set return transfer to true to get the response as a string
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Optionally, you can set the request type, e.g., POST, GET, PUT
// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

// If you want to send JSON or other headers, you can do so here
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
   'Content-Type: application/json',
   'Accept: application/json'
));

// Execute the request and fetch the response
$response = curl_exec($ch);

// Check for errors
if(curl_errno($ch)) {
    echo 'cURL error: ' . curl_error($ch);
} else {
    // Output the response
    echo $response;
}

// Close the cURL session
curl_close($ch);
?>
