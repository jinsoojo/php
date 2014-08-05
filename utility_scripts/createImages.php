<?php

/*
 * Quick script to generate some image w/ text + random poly to be used in some upload tests.
 */

$imgsDir = getcwd() . DIRECTORY_SEPARATOR . 'images';
if (!file_exists($imgsDir))
{
	echo "Creating directory @ $imgsDir\n";
	// 	>>> WINDOWS CMD!! <<<
	exec("MKDIR $imgsDir");
}

$filenamePrefix = 'attachTest_';
$r = 0;
$g = 1;
$b = 2;
$images = array(
	array(
		'width' => 30,
		'height' =>  30,
		'rgb' => array(0, 0, 0),
	),
	array(
		'width' => 60,
		'height' =>  60,
		'rgb' => array(0, 0, 255),
	),
	array(
		'width' => 10,
		'height' =>  100,
		'rgb' => array(0, 255, 0),
	),
	array(
		'width' => 20,
		'height' =>  100,
		'rgb' => array(0, 255, 255),
	),
	array(
		'width' => 100,
		'height' =>  100,
		'rgb' => array(255, 0, 0),
	),
	array(
		'width' => 100,
		'height' =>  10,
		'rgb' => array(255, 0, 255),
	),
	array(
		'width' => 100,
		'height' =>  20,
		'rgb' => array(255, 255, 0),
	),
	array(
		'width' => 500,
		'height' =>  500,
		'rgb' => array(255, 255, 255),
	),
);


// DRAW IMAGES

foreach ($images AS $info)
{
	$width = $info['width'];
	$height = $info['height'];
	$rgb = $info['rgb'];

	$im = imagecreatetruecolor($width, $height);
	// Create some colors
	$rgb_neg = array(
		((0xFF - $rgb[$r])),
		((0xFF - $rgb[$g])),
		((0xFF - $rgb[$b])),
	);

	$color = imagecolorallocate($im, $rgb[$r], $rgb[$g], $rgb[$b]);
	imagefilledrectangle($im, 0, 0, $width, $height, $color);

	$filename = $filenamePrefix . $width . "x" . $height;
	$color_neg = imagecolorallocate($im, $rgb_neg[$r], $rgb_neg[$g], $rgb_neg[$b]);

	// add some randomness
	$num_points = 0;
	$i = 0;
	while($num_points < 3)
	{
		$i++;
		if ($i > 50)
		{
			// what are the chances that we get
			echo "\nSomething went wrong. We keep getting the same 2 (or less) randomly generated points for 50 attempts.";
			echo "\nWow, RNG frowns upon you this day. We need at least 3 points for a polygon.\nExiting with tears.";
			exit;
		}
		$num_points = rand(3, 50);
		$vertices = array();			// keep track of dupes
		$verticies_serial = array(); // because imagefilledpolygon requires a serial list of x1, y1, x2, y2...xn, yn
		for ($n = 0; $n < $num_points; $n++)
		{
			$x = rand(0,$width);
			$y = rand(0,$height);
			$key=(string)($x . "" . $y); // ghetto dupe tracker.
			$vertices[$key] = true;
			$verticies_serial[] = $x;
			$verticies_serial[] = $y;
		}
		$num_points = count($vertices); // in case rand() above produced any duplicate vertices
	}

	imagefilledpolygon($im, $verticies_serial, $num_points, $color_neg);

	// draw string
	$color_black = imagecolorallocate($im, 255, 255, 255);
	$color_white = imagecolorallocate($im, 0, 0, 0);
	$font = 1;
	//$textBorderArr = array(array(-1, -1), array(-1, 1), array(1, -1), array(1, 1));
	$textBorderArr = array(array(0, -1), array(0, 1), array(-1, 0), array(1, 0));
	if ($width >= $height)
	{
		$wrapwidth =  ($width / imagefontwidth($font));
		$text = wordwrap($filename, $wrapwidth, "\n", true);
		foreach(explode("\n", $text) AS $n => $line)
		{
			$hoffset = imagefontheight($font) * ($n);
			$x = 3;
			$y = 3 + $hoffset;
			// ghetto way to draw border around font
			foreach ($textBorderArr AS $offset)
			{
				imagestring($im, $font, $x + $offset[0], $y + $offset[1], $line, $color_white);
			}
			imagestring($im, $font, $x, $y, $line, $color_black);
		}
	}
	else
	{
		$wrapwidth =  ($height / imagefontwidth($font));
		$text = wordwrap($filename, $wrapwidth, "\n", true);
		foreach(explode("\n", $text) AS $n => $line)
		{
			$woffset = imagefontheight($font) * ($n);
			$x = 3 + $woffset;
			$y = $height - 3;
			// ghetto way to draw border around font
			foreach ($textBorderArr AS $offset)
			{
				imagestringup($im, $font, $x + $offset[0], $y + $offset[1], $text, $color_white);
			}
			imagestringup($im, $font, $x, $y, $text, $color_black);
		}

	}


	// Write to file
	$fullfilepath =  $imgsDir . DIRECTORY_SEPARATOR . $filename . ".png";
	imagepng($im, $fullfilepath);
	echo "Created $fullfilepath\n";
	imagedestroy($im);
}

echo "Done!\n";

?>