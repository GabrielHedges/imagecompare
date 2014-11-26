<?php

function image_comparison_array_create($filename) {
    $i = new Imagick(realpath($filename)); // load image
    $i->scaleImage(6,6); //scale to 6x6 thumbnail
    $i->setImageType(2); //convert to greyscale
    $ii = ($i->getImageChannelMean(imagick::CHANNEL_GRAY)); // get the mean value for all pixels in thumbnail

    //calculate different between each pixel and the thumbnail mean value
    $diff = array();
    for ($y = 0; $y < 6; $y++) {
        for ($x = 0; $x < 6; $x++) {
            $p = $i->getImagePixelColor($x,$y);  //get pixel object
            $rgb = $p->getColor(); // get rgb values
            $id = ($ii['mean'] - $rgb['r']); //find difference between pixel and mean of thumbnail. Any channel works, as all are the same due to setImageType(2)
            $nid = (int) round(($id - -255) / 2); //normalize image difference between 0 - 255 so it fits in an unsigned 8 bit integer
            $diff[] = $nid; // add to array
        }
    }
    $compareArray = array('mean' => (int) round($ii['mean']), 'dev' => (int) round($ii['standardDeviation']), 'diff' => $diff);
    return $compareArray;
}

function image_compare_arrays($a1, $a2) {
	//compares array #1 with array #2 and returns similarity percentage
	$perArray = array();
	foreach($a1 as $i => $a1v) {
		$perArray[] = $a1v / $a2[$i];
	}
	$perSum = array_sum($perArray);
	return abs(1 - abs($perSum / count($a1)));
}