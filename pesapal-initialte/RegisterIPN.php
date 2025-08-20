<?php
require_once 'acesstoken.php';

function registerIPN($token) {
    $ipnUrl = "https://68a5dd61000f0d68c3d0.fra.appwrite.run/";
    
    if(APP_ENVIROMENT == 'sandbox'){
        $ipnRegistrationUrl = "https://cybqa.pesapal.com/pesapalv3/api/URLSetup/RegisterIPN";
    }elseif(APP_ENVIROMENT == 'live'){
        $ipnRegistrationUrl = "https://pay.pesapal.com/v3/api/URLSetup/RegisterIPN";
    }else{
        throw new Exception("Invalid APP_ENVIROMENT");
    }

    $headers = array(
        "Accept: application/json",
        "Content-Type: application/json",
        "Authorization: Bearer $token"
    );

    $data = array(
        "url" => $ipnUrl, 
        "ipn_notification_type" => "POST" 
    );

    $ch = curl_init($ipnRegistrationUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($responseCode !== 200) {
        throw new Exception("Failed to register IPN. HTTP Code: " . $responseCode);
    }

    $data = json_decode($response);
    if (!$data || !isset($data->ipn_id) || !isset($data->url)) {
        throw new Exception("Invalid IPN registration response: " . $response);
    }

    return array(
        'ipn_id' => $data->ipn_id,
        'url' => $data->url
    );
}

// Only execute if this file is called directly
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    try {
        $token = getAccessToken();
        if (!$token) {
            throw new Exception("Failed to get access token");
        }
        
        $result = registerIPN($token);
        header('Content-Type: application/json');
        echo json_encode($result);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}



