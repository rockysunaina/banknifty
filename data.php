<?php

// Set the time zone
date_default_timezone_set('Asia/Kolkata');

// Define the symbol
$symbol = "BANKNIFTY";

// Function to fetch and save data
function fetch_and_save_data($symbol) {
    // URL to fetch data
    $url = "https://webapi.niftytrader.in/webapi/option/fatch-option-chain?symbol=" . strtolower($symbol) . "&expiryDate=";

    // Headers for the request
    $headers = [
        "Accept: application/json, text/plain, */*",
        "Accept-Language: en-GB,en-US;q=0.9,en;q=0.8",
        "Authorization: Bearer YOUR_BEARER_TOKEN_HERE",
        "Cache-Control: no-cache",
        "Connection: keep-alive",
        "Origin: https://www.niftytrader.in",
        "Pragma: no-cache",
        "Referer: https://www.niftytrader.in/",
        "Sec-Fetch-Dest: empty",
        "Sec-Fetch-Mode: cors",
        "Sec-Fetch-Site: same-site",
        "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/127.0.0.0 Safari/537.36",
        "platform_type: 1",
        "sec-ch-ua: \"Not)A;Brand\";v=\"99\", \"Google Chrome\";v=\"127\", \"Chromium\";v=\"127\"",
        "sec-ch-ua-mobile: ?0",
        "sec-ch-ua-platform: \"Windows\""
    ];

    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    // Execute cURL and get the response
    $response = curl_exec($ch);
    curl_close($ch);

    // Decode the JSON response
    $data = json_decode($response, true);

    // Get the current date and time
    $currentDate = date("Ymd");
    $currentTime = date("Hi");

    // Check if the response is valid
    if (isset($data['resultData']['opExpiryDates'])) {
        $expiryDates = $data['resultData']['opExpiryDates'];

        foreach ($expiryDates as $expiryDate) {
            // Filter data for the specific expiry date
            $expiryData = array_filter($data['resultData']['opDatas'], function($item) use ($expiryDate) {
                return $item['expiry_date'] === $expiryDate;
            });

            // Format the expiry date to remove invalid characters
            $formattedExpiryDate = str_replace(":", "-", explode("T", $expiryDate)[0]);

            // Create the directory structure
            $saveFolder = __DIR__ . "/haru/" . strtolower($symbol) . "/$currentDate/$formattedExpiryDate";
            if (!file_exists($saveFolder)) {
                mkdir($saveFolder, 0777, true);
            }

            // Define the file path
            $filePath = "$saveFolder/$currentTime.json";

            // Save the data to a JSON file
            file_put_contents($filePath, json_encode($expiryData, JSON_PRETTY_PRINT));

            echo "Data saved for $symbol at $filePath\n";
        }
    } else {
        echo "Failed to fetch data for $symbol at " . date("H:i") . ".\n";
    }
}

// Function to run the fetching process every 3 minutes
function run_for_interval($interval_minutes, $end_time) {
    do {
        $start_time = time();
        fetch_and_save_data("BANKNIFTY");

        // Calculate time taken and wait for the next interval
        $time_taken = time() - $start_time;
        echo "Job completed in $time_taken seconds.\n";

        sleep(($interval_minutes * 60) - $time_taken);
    } while (time() < $end_time);
}

// Set the start time (09:15) and end time (15:30)
$start_time = strtotime('09:15:00');
$end_time = strtotime('15:30:00');

// If the current time is before the start time, wait until the start time
if (time() < $start_time) {
    sleep($start_time - time());
}

// Run the fetching process every 3 minutes until 15:30
run_for_interval(3, $end_time);

?>
