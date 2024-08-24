<?php
// Execute the Python script
exec("python3 fetch_banknifty_data.py");

// Find the latest JSON file created by the Python script
$dir = 'banknifty/';
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$latest_file = '';
$latest_time = 0;

foreach ($files as $file) {
    if ($file->isFile()) {
        $file_time = $file->getMTime();
        if ($file_time > $latest_time) {
            $latest_time = $file_time;
            $latest_file = $file->getRealPath();
        }
    }
}

if ($latest_file) {
    // Serve the latest JSON file for download
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename=' . basename($latest_file));
    header('Content-Length: ' . filesize($latest_file));
    readfile($latest_file);
} else {
    echo "No data available for download.";
}
?>
