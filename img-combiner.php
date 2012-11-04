#!/usr/bin/env php
<?php
if (PHP_VERSION_ID < 50300) {
    die("PHP version " . PHP_VERSION . " is not supported. Version 5.3+ is required.\n");
}

// Imagick is required
if (!extension_loaded('imagick')) {
    die("PHP extension Imagick is required but not loaded.\n");
}

if (!is_writable(__DIR__)) {
    die("Directory '" . __DIR__ . "' is not writable.\n");
}

// init
$dir	= __DIR__;
$files	= array();
$types	= array('png');
$cols	= 20; // images in line
$format = 'png';    // default output format
$margin = 0;	// margin around each image

// options
$allowedOptions = array(
    '-d' => array('<path>', 'Scan images in this directory.'),
    '-f' => array('<files>', "List of images divided by comma in given directory, e.g. 'img1.png,img2.jpg,...'."),
    '-t' => array('<types>', "Types (extensions) of images, e.g. 'png,jpg,...'."),
    '-c' => array('<number>', "Count of images in one line. Default is $cols."),
    '-o' => array('<format>', "Output image format, e.g. 'png'. Default is $format."),
    '-m' => array('<number>', "Margin in pixels around each image. Default is {$margin}px."),
    '-h' => array('', 'Display this help message.')
);
$allowedTypes = array('png', 'gif', 'jpg', 'jpeg');
$allowedOutputFormats = array('png', 'gif', 'jpg');
$options = array_keys($allowedOptions);

if ($argc < 2 || $argv[1] == '-h') {
    displayHelp($allowedOptions);
}

array_shift($argv); // remove script name

// process input params
while (list($key, $arg) = each($argv)) {
    if (!in_array($arg, $options)) {
	die("Invalid option '$arg'.\n");
    }

    $value = current($argv);

    if (empty($value)) {
	die("Missing value for option '$arg'.\n");
    }

    // check options
    switch ($arg) {
	case '-d':  // directory
	    if (!is_dir($value)) {
		die("'$value' is not directory.\n");
	    }

	    if (!is_readable($value)) {
		die("Directory '$value' is not readable.\n");
	    }

	    $dir = rtrim($value, '/');
	    break;
	case '-f':  // files
	    $_files = explode(',', $value);
	    array_walk($_files, 'trim');

	    if (!empty($_files)) {
		$files = $_files;
	    }

	    break;
	case '-t': // types
	    $_types = explode(',', $value);
	    array_walk($_types, 'trim');

	    if (is_array($_types)) {
		foreach ($_types as $type) {
		    if (!in_array($type, $allowedTypes)) {
			die("Image type '$type' is not allowed.\n");
		    }
		}

	    }

	    $types = $_types;
	    break;
	case '-c':  // count of images in line
	    if (!ctype_digit($value)) {
		die("Option '$arg' must be a number.\n");
	    }

	    $value = (int) $value;
	    if ($value < 1) {
		die("Invalid images count in line. Given '$value'.\n");
	    }

	    $cols = $value;
	    break;
	case '-o': // output format
	    if (!in_array($value, $allowedOutputFormats)) {
		$strFormats = implode(',', $allowedOutputFormats);
		die("Only $strFormats output formats are supported. $value given.\n");
	    }

	    $format = $value;
	    break;
	case '-m': // margin around each image
	    if (!ctype_digit($value)) {
		die("Option '$arg' must be a number.\n");
	    }

	    $value = (int) $value;
	    if ($value < 0) {
		die("Margin around images cannot be less than 0px. Given '$value'.");
	    }

	    $margin = $value;
	    break;
	default:
	    break;
    }

    next($argv);
}

// set files in given directory
if (empty($files)) {
    $pattern = "~\.(" . implode('|', $types) . ")$~i";
    foreach (glob("$dir/*") as $file) {
	if (preg_match($pattern, $file)) {
	    $files[] = $file;
	}
    }
}

// load files set in input option
$images = array();
foreach ($files as $f) {
    $f = pathinfo(trim($f, '/'), PATHINFO_BASENAME);
    $file = "$dir/$f";
    $extension = pathinfo($file, PATHINFO_EXTENSION);

    if (!is_readable($file)) {
	die("File '$file' does not exist or is not readable.\n");
    }

    if (!in_array(strtolower($extension), $types)) {
	$strTypes = implode(',', $types);
	die("File '$file' with extension '$extension' is not allowed. Only $strTypes are supported.\n");
    }

    $images[] = $file;
}

try {
    $totalImages = 0;
    $startTime = microtime(true);

    // create one icon from more files
    $imagesInLine = new Imagick();
    $combinedImages = new Imagick();
    $empty = true;	// any icon to process yet?

    foreach ($images as $index => $image) {
	$img = new Imagick($image);
	$img->borderimage(($format == 'jpg' ? 'white' : 'transparent'), $margin, $margin);
	if ($ok = $imagesInLine->addimage($img)) {
	    $totalImages++;
	}
	echo ($index + 1) . ") Adding: $image ... " . ($ok ? 'ok' : 'error') . "\n";
	$empty = false;

	if ($totalImages % $cols == 0) {	// wrap images in line
	    $imagesInLine->resetiterator();
	    $combinedImages->addImage($imagesInLine->appendimages(false));
	    $imagesInLine = new Imagick();
	    $empty = true;
	}
    }

    // add rest of icons
    if (!$empty) {
	$imagesInLine->resetiterator();
	$lastImagesInLine = $imagesInLine->appendimages(false);
	$combinedImages->addimage($lastImagesInLine);
    }

    $outputFile = __DIR__ . "/combined_images.$format";

    $combinedImages->resetiterator();
    $finalImages = $combinedImages->appendimages(true);
    $finalImages->setimageformat($format);
    $finalImages->setimagefilename($outputFile);
    if ($finalImages->writeimage()) {
	$totalTime = round(microtime(true) - $startTime, 3);

	echo "\n\n";
	echo "$totalImages image" . ($totalImages === 1 ? '' : 's') . " combined into image '$outputFile' in $totalTime seconds.\n";
	exit(0);
    } else {
	die("Error while creating image '$outputFile'.");
    }
} catch (Exception $e) {
    $msg = $e->getMessage();
    die("Imagick error: $msg.\n");
}

//---------------------------------- functions -------------------------------//

function displayHelp(array $options)
{
    echo "Usage: [options] [arguments]\n";
    foreach ($options as $option => $info) {
	echo " $option " . ($info[0] ?: "\t") . "\t$info[1]\n";
    }
    echo "\n";
    exit(0);
}

exit(0);
?>