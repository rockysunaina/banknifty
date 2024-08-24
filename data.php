<?php
// Execute the Python script
exec("python3 fetch_banknifty_data.py");

// Zip the directory containing the fetched data
$zip = new ZipArchive();
$filename = "banknifty_data.zip";

if ($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
    exit("Cannot open <$filename>\n");
}

$dir = 'banknifty/';

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($dir),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $file) {
    if (!$file->isDir()) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($dir));
        $zip->addFile($filePath, $relativePath);
    }
}

$zip->close();

// Serve the zip file for download
header('Content-Type: application/zip');
header('Content-disposition: attachment; filename=' . $filename);
header('Content-Length: ' . filesize($filename));
readfile($filename);

// Clean up the zip file after download
unlink($filename);
?>
