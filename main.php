<?php
# Includes the autoloader for libraries installed with composer
require __DIR__ . '/vendor/autoload.php';

include "gcp_detect_text.php";
include "gd_functions.php";

# Your Google Cloud Platform project ID
$projectId = 'YOUR_PROJECT_ID';
#$path = '/home/marcos/Downloads/uf009404.gif';
#$path = '/home/marcos/Downloads/tumblr_otr1nuStWO1u1iysqo1_1280.png';
#$path = '/home/marcos/Downloads/tirinhas167.jpg';
$path = '/home/marcos/Downloads/tumblr_ouw2l1bWim1r7ni1io1_1280.jpg';

setlocale(LC_ALL, 'pt_BR');
$lc = setlocale(LC_NUMERIC, '0');
echo $lc;
$image = gd_create_image($path);
if ($image == "UNSUPPORTED") {
	echo "Image type not supported, please submit BMP, GIF, PNG or JPG images.";
	exit;
}
if ($image == "BROKEN") {
	echo "Error creating image";
	exit;
}
$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);
$image_width = imagesx($image);
$image_height = imagesy($image);

$bw_image = imagecreatetruecolor($image_width, $image_height);
$bw_big_image = imagecreatetruecolor($image_width*2, $image_height*2);
$big_image = imagecreatetruecolor($image_width*2, $image_height*2);
$big_bw_image = imagecreatetruecolor($image_width*2, $image_height*2);
imagecopy($bw_image, $image, 0, 0, 0, 0, $image_width, $image_height);
imagefilter($bw_image, IMG_FILTER_GRAYSCALE);
imagefilter($bw_image, IMG_FILTER_CONTRAST, -1000);
imagecopyresampled ($bw_big_image, $bw_image, 0, 0, 0, 0, $image_width*2, $image_height*2, $image_width, $image_height);
imagecopyresampled ($big_image, $image, 0, 0, 0, 0, $image_width*2, $image_height*2, $image_width, $image_height);
imagecopy ($big_bw_image, $big_image, 0, 0, 0, 0, $image_width*2, $image_height*2);
imagefilter($big_bw_image, IMG_FILTER_GRAYSCALE);
imagefilter($big_bw_image, IMG_FILTER_CONTRAST, -1000);

$img_array = array($image, $bw_image, $big_image, $bw_big_image, $big_bw_image);
$image_name_array = array("image", "bw_image", "big_image", "bw_big_image"   , "big_bw_image");
foreach( $img_array as $img) {
$name_index=0;

ob_start(); // start a new output buffer
#  imagepng($img);
  imagejpeg($img,NULL,90);
  $img_data = ob_get_contents();
ob_end_clean(); // stop this output buffer

$document =  gcp_detect_text($projectId, $img_data);
#$document =  gcp_detect_text($projectId, $path);


foreach ($document->pages() as $page) {
	foreach ($page['blocks'] as $block) {
		$block_number = 0;
		$vertice_index = 0;
		$block_text = '';
		$current_word = "";
		$words = 0;
		$block_words = array();
		$block_lines = array();
		foreach ($block['paragraphs'] as $paragraph) {
		$y_word=0;
			foreach ($paragraph['words'] as $word) {
				foreach ($word['symbols'] as $symbol) {
					$block_text .= $symbol['text'];
					$current_word .= $symbol['text'];
				}
				$block_words[] = $current_word;
				$current_word = "";
				$block_text .= ' ';
				$words++;
				$word_height = $word['boundingBox']['vertices'][3]['y']-$word['boundingBox']['vertices'][0]['y'];
				if ($word['boundingBox']['vertices'][3]['y'] > $y_word + $word_height) {
					$block_lines[]=$word['boundingBox']['vertices'][3];
					$y_word = $word['boundingBox']['vertices'][3]['y'];
				}
			}
#			var_export($block_lines);
		}
		$image_name = $image_name_array[$name_index];
		$block_text_array[$image_name][$block_number] = $block_text;
		$block_number++;
		
#		printf('Block text: %s' . PHP_EOL, $block_text);
#	        printf('Block bounds:' . PHP_EOL);
		foreach ($block['boundingBox']['vertices'] as $vertice) {
#			printf('X: %s Y: %s' . PHP_EOL, $vertice['x'], $vertice['y']);
			$x_temp[$vertice_index] = $vertice['x'];
			$y_temp[$vertice_index] = $vertice['y'];
			$vertice_index++;
		}
/*
		$x1_block = $x_temp[0];
		$x2_block = $x_temp[2];
		$y1_block = $y_temp[1];
		$y2_block = $y_temp[3];    
		$block_width = $x2_block-$x1_block;
		$block_height = $y2_block-$y1_block;
		$words_per_line = $words/count($block_lines);
		imagefilledrectangle($image , $x1_block , $y1_block , $x2_block , $y2_block , $white);
		$font = "/usr/share/fonts/dejavu/DejaVuSans.ttf";
		#$font = "/usr/share/fonts/julietaula-montserrat/MontserratAlternates-ExtraLight.ttf";
		#$font = "/usr/share/fonts/julietaula-montserrat/MontserratAlternates-Light.ttf";
		$linecount=0;
		$line_text="";
		foreach ($block_words as $word) {
#			$line_height = $block_lines[0][3]['y'] - $block_lines[0][0]['y'] - 3;
			$line_height = $word_height - 1;
			$old_box_array = imagettfbbox ($line_height , 0, $font, $line_text);
			$old_box_width = $old_box_array[2] - $old_box_array[0];
			if (!ctype_alpha(utf8_decode($word))) {
				$line_text=rtrim($line_text);
			}
			$old_line_text = $line_text;
			$line_text .= $word;
			$line_width = $block_width;
			$new_box_array = imagettfbbox ($line_height , 0, $font, $line_text);
			$new_box_width = $new_box_array[2] - $new_box_array[0];
			printf('Word: %s\ Old_Line: %s\ New_Line: %s\ Block width: %s\ Line box width: %s' . PHP_EOL, $word, $old_line_text, $line_text,$block_width,$new_box_width);
			if ($block_width < $new_box_width) {
				imagefttext($image, $line_height, 0, $block_lines[$linecount]['x'], $block_lines[$linecount]['y'], $black, $font, $old_line_text);
				$line_text = $word." ";
				$linecount++;
				$new_line = true;
			} else {
				if ($word != "'") {
					$line_text .= ' ';
				}
				$new_line = false;
			 }
		}
		echo $old_line_text. PHP_EOL;
		echo $line_text;
		if (!$new_line) {
			imagefttext($image, $line_height, 0, $block_lines[$linecount]['x'], $block_lines[$linecount]['y'], $black, $font, $line_text);
		}
*/

	}
$name_index++;
}
} 
var_export($block_text_array);
	imagejpeg ($image, "image.jpg",95); 
	imagejpeg ($bw_image, "bw_image.jpg",95); 
	imagejpeg ($big_image, "big_image.jpg",95); 
	imagejpeg ($bw_big_image, "bw_big_image.jpg",95); 
	imagejpeg ($big_bw_image, "big_bw_image.jpg",95); 

?>
