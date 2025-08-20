<?php
require_once 'acesstoken.php';
require_once 'RegisterIPN.php';

function submitOrder($token, $ipn_id) {
    $merchantreference = rand(1, 1000000000000000000);
    $phone = "0795065125";
    $amount = 1.00;
    $callbackurl = "https://68a5dd61000f0d68c3d0.fra.appwrite.run/";
    $branch = "ydb";
    $first_name = "Ian";
    $middle_name = "Munguti";
    $last_name = "Mutunga";
    $email_address = "mungutiian98@gmail.com";

    if(APP_ENVIROMENT == 'sandbox'){
        $submitOrderUrl = "https://cybqa.pesapal.com/pesapalv3/api/Transactions/SubmitOrderRequest";
    }elseif(APP_ENVIROMENT == 'live'){
        $submitOrderUrl = "https://pay.pesapal.com/v3/api/Transactions/SubmitOrderRequest";
    }else{
        throw new Exception("Invalid APP_ENVIROMENT");
    }

    $headers = array(
        "Accept: application/json",
        "Content-Type: application/json",
        "Authorization: Bearer $token"
    );

    // Request payload
    $data = array(
        "id" => $merchantreference,
        "currency" => "KES",
        "amount" => $amount,
        "description" => "Payment description goes here",
        "callback_url" => $callbackurl,
        "notification_id" => $ipn_id,
        "branch" => $branch,
        "billing_address" => array(
            "email_address" => $email_address,
            "phone_number" => $phone,
            "country_code" => "KE",
            "first_name" => $first_name,
            "middle_name" => $middle_name,
            "last_name" => $last_name,
            "line_1" => "Pesapal Limited",
            "line_2" => "",
            "city" => "",
            "state" => "",
            "postal_code" => "",
            "zip_code" => ""
        )
    );

    $ch = curl_init($submitOrderUrl);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("Failed to submit order. HTTP Code: " . $httpCode);
    }

    $result = json_decode($response);
    if (!$result) {
        throw new Exception("Invalid response from order submission: " . $response);
    }

    return $result;
}

// Only execute if this file is called directly
if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    try {
        $token = getAccessToken();
        if (!$token) {
            throw new Exception("Failed to get access token");
        }

        $ipnResult = registerIPN($token);
        if (!isset($ipnResult['ipn_id'])) {
            throw new Exception("Failed to get IPN ID");
        }

        $result = submitOrder($token, $ipnResult['ipn_id']);
        
        if (isset($result->redirect_url)) {
            header("Location: " . $result->redirect_url);
            exit;
        } else {
            header('Content-Type: application/json');
            echo json_encode($result);
        }
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

// Request payload
$data = array(
    "id" => "$merchantreference",
    "currency" => "KES",
    "amount" => $amount,
    "description" => "Payment description goes here",
    "callback_url" => "$callbackurl",
    "notification_id" => "$ipn_id",
    "branch" => "$branch",
    "billing_address" => array(
        "email_address" => "$email_address",
        "phone_number" => "$phone",
        "country_code" => "KE",
        "first_name" => "$first_name",
        "middle_name" => "$middle_name",
        "last_name" => "$last_name",
        "line_1" => "Pesapal Limited",
        "line_2" => "",
        "city" => "",
        "state" => "",
        "postal_code" => "",
        "zip_code" => ""
    )
);
$ch = curl_init($submitOrderUrl);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$data = json_decode($response);
$redirect_Url = $data->redirect_url;

//echo "<a href='$redirect_Url'>Pay Now</a>";
echo "<script type='text/javascript'>window.location.href='". $data->redirect_url ."';</script>";