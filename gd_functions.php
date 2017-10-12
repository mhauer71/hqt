<?php
function gd_create_image($url){
	$image_type = exif_imagetype($url);
	switch($image_type) {
		case IMAGETYPE_BMP:
			$gd_image = imagecreatefrombmp($url);
			break;	
		case IMAGETYPE_GIF:
			$gd_image = imagecreatefromgif($url);
			break;	
			
		case IMAGETYPE_PNG:
			$gd_image = imagecreatefrompng($url);
			break;	
		case IMAGETYPE_JPEG:
			$gd_image = imagecreatefromjpeg($url);
			break;	
		default:
			return("UNSUPPORTED");
	}
	if (!$gd_image) {
		return("BROKEN");
	}
	return($gd_image);
}
/*
function gd_get_box_color($image, $vertice) {
	$background = imagecolorat($image, $vertice['x'], $vertice['y']);
	return $background;
}
function getAverage($image_block){
    $scaled = imagescale($image_block, 1, 1, IMG_BICUBIC); 
    $index = imagecolorat($scaled, 0, 0);
    $rgb = imagecolorsforindex($scaled, $index); 
    $red = $rgb['red']; 
    $green = $rgb['green']; 
    $blue = $rgb['blue']; 
    #$red = round(round(($rgb['red'] / 0x33)) * 0x33); 
    #$green = round(round(($rgb['green'] / 0x33)) * 0x33); 
    #$blue = round(round(($rgb['blue'] / 0x33)) * 0x33); 
    return (['red' => $red, 'green' => $green, 'blue' => $blue]); 
} 
 */
function colorPalette($img, $size, $numColors, $granularity = 1) 
{ 
   $granularity = max(1, abs((int)$granularity)); 
   $colors = array(); 
#   $size = @getimagesize($img); 
#   echo $size;
   for($x = 0; $x < $size[0]; $x += $granularity) 
   { 
      for($y = 0; $y < $size[1]; $y += $granularity) 
      { 
         $thisColor = imagecolorat($img, $x, $y); 
         $rgb = imagecolorsforindex($img, $thisColor); 
         $red = round(round(($rgb['red'] / 0x33)) * 0x33); 
         $green = round(round(($rgb['green'] / 0x33)) * 0x33); 
         $blue = round(round(($rgb['blue'] / 0x33)) * 0x33); 
         $thisRGB = sprintf('%02X%02X%02X', $red, $green, $blue); 
         if(array_key_exists($thisRGB, $colors)) 
         { 
            $colors[$thisRGB]++; 
         } 
         else 
         { 
            $colors[$thisRGB] = 1; 
         } 
      } 
   } 
   arsort($colors); 
   return array_slice(array_keys($colors), 0, $numColors); 
} 
?>
