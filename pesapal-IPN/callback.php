<?php
header("Content-Type: application/json");
$pinCallbackData = file_get_contents('php://input');
$logFile = "pin.json";
$log = fopen($logFile, "a");
fwrite($log, $pinCallbackData);
fclose($log);