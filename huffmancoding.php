<?php
$txt = /*"hooman naghiee"*/file_get_contents ( "./txt/jquery-2.1.1.txt" );
// $txt = file_get_contents ( "./txt/1.txt" );
// $txt = "hooman naghiee";
// **************************huffmancoding
$txt = htmlspecialchars ( $txt );
function encode($symb2freq) {
	$heap = new SplPriorityQueue ();
	$heap->setExtractFlags ( SplPriorityQueue::EXTR_BOTH );
	foreach ( $symb2freq as $sym => $wt )
		$heap->insert ( array (
				$sym => '' 
		), - $wt );
	
	while ( $heap->count () > 1 ) {
		$lo = $heap->extract ();
		$hi = $heap->extract ();
		foreach ( $lo ['data'] as &$x )
			$x = '0' . $x;
		foreach ( $hi ['data'] as &$x )
			$x = '1' . $x;
		$heap->insert ( $lo ['data'] + $hi ['data'], $lo ['priority'] + $hi ['priority'] );
	}
	$result = $heap->extract ();
	return $result ['data'];
}
function string_to_binery($txt, $debug = false) {
	if (is_null ( $txt ))
		return null;
	$symb2freq = array_count_values ( str_split ( $txt ) );
	$symb2freq ['END'] = 0;
	asort ( $symb2freq );
	$huff = encode ( $symb2freq );
	if ($debug) {
		echo "<table><td>Symbol</td><td>Weight</td><td>Huffman Code</td>";
		foreach ( $symb2freq as $sym => $code )
			echo "<tr><td>{$sym}</td><td>{$code}</td><td>{$huff[$sym]}</td></tr>";
		echo "</table>";
	}
	asort ( $huff );
	$array = array (
			0 => strtr ( $txt, $huff ) . $huff ['END'] . $huff ['END'] . $huff ['END'] . $huff ['END'],
			1 => $huff 
	);
	return $array;
}
function binery_to_string($array) {
	if (is_null ( $array ))
		return null;
	$array [1] = unserialize ( $array [1] );
	$array [1] = array_flip ( $array [1] );
	$string = strtr ( $array [0], $array [1] );
	return $string;
}
// **************************huffmancoding end

// **************************huffmancoding to img
function binery_to_image_1($txt) {
	$binery = string_to_binery ( $txt );
	$binerylen = strlen ( $binery [0] );
	$imgx = 1000;
	$imgy = round ( $binerylen / $imgx ) + 1;
	$img = imagecreatetruecolor ( $imgx, $imgy );
	$white = imagecolorallocate ( $img, 255, 255, 255 );
	$y = 0;
	$x = 0;
	for($i = 0; $i <= $binerylen + 1; $i ++) {
		if ($x >= $imgx) {
			$y ++;
			$x = 0;
		}
		if (substr ( $binery [0], $i, 1 ) == 1)
			imagesetpixel ( $img, $x, $y, $white );
		
		$x ++;
	}
	header ( 'Content-Type: image/png' );
	imagepng ( $img, null, 9 );
}
/**
 * just for fun.
 */
function binery_to_image_3($txt) {
	$binery = string_to_binery ( $txt );
	$binerylen = strlen ( $binery [0] );
	$imgx = 500;
	$imgy = round ( ($binerylen / 3) / $imgx ) + 1;
	$img = imagecreatetruecolor ( $imgx, $imgy );
	$y = 0;
	$x = 0;
	for($i = 0; $i <= ($binerylen / 3) + 3; $i ++) {
		if ($x >= $imgx) {
			$y ++;
			$x = 0;
		}
		$bits = substr ( $binery [0], $i * 3, 3 );
		$bit1 = substr ( $bits, 0, 1 ) == 1 ? 255 : 0;
		$bit2 = substr ( $bits, 1, 1 ) == 1 ? 255 : 0;
		$bit3 = substr ( $bits, 2, 1 ) == 1 ? 255 : 0;
		$color = imagecolorallocate ( $img, $bit1, $bit2, $bit3 );
		imagesetpixel ( $img, $x, $y, $color );
		
		$x ++;
	}
	header ( 'Content-Type: image/png' );
	imagepng ( $img );
}
function binery_to_image_24($txt) {
	$binery = string_to_binery ( $txt );
	if (isset ( $_GET ['base'] )) {
		$array = array ();
		foreach ( $binery [1] as $key => $val )
			$array [$val] = utf8_encode ( $key );
		echo json_encode ( $array );
	} elseif (isset ( $_GET ['b'] )) {
		echo $binery [0];
	} else {
		$binerylen = strlen ( $binery [0] );
		if ($binerylen / 24 <= 500) {
			$imgx = $binerylen / 24 + 1;
		} else {
			$imgx = 500;
		}
		$imgy = ceil ( ($binerylen / 24) / $imgx );
		$img = imagecreatetruecolor ( $imgx, $imgy );
		$y = 0;
		$x = 0;
		for($i = 0; $i <= ($binerylen / 24) + 24; $i ++) {
			if ($x >= $imgx) {
				$y ++;
				$x = 0;
			}
			$bites = substr ( $binery [0], $i * 24, 24 );
			$bite1 = bindec ( substr ( $bites, 0, 8 ) );
			$bite2 = bindec ( substr ( $bites, 8, 8 ) );
			$bite3 = bindec ( substr ( $bites, 16, 8 ) );
			$color = imagecolorallocate ( $img, $bite1, $bite2, $bite3 );
			imagesetpixel ( $img, $x, $y, $color );
			
			$x ++;
		}
		header ( 'Content-Type: image/png' );
		imagepng ( $img );
	}
}
// **************************huffmancoding to img end

if (isset ( $_GET ['t'] ) && $_GET ['t'] == 1)
	binery_to_image_1 ( $txt );
elseif (isset ( $_GET ['t'] ) && $_GET ['t'] == 3)
	binery_to_image_3 ( $txt );
else
	binery_to_image_24 ( $txt );
?>