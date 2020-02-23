<?php
/**
 * @version 0.1
 * @author recens
 * @license GPL
 * @copyright Geltishheva Nina (http://recens.ru)
 */

use ColorThief\ColorThief;

class ResizeCrop {

	const types = array(
		0 => '',
		1 => 'gif',
		2 => 'jpeg',
		3 => 'png',
		18 => 'webp'
	);

	/**
	 * @param $file_input
	 * @param $file_output
	 * @param $w_o
	 * @param $h_o
	 * @param bool $percent
	 * @return mixed
	 */
	public static function resize($file_input, $file_output, $w_o, $h_o, $percent = false) {

		list($w_i, $h_i, $type) = getimagesize($file_input);
		if (!$w_i || !$h_i) {return false;}

		$ext = self::types[$type];
		if ($ext) {
			$func = 'imagecreatefrom' . $ext;
			$img = $func($file_input);
		} else {
			return false;
		}

		if ($percent) {
			$w_o *= $w_i / 100;
			$h_o *= $h_i / 100;
		}

		if (!$h_o) $h_o = $w_o / ($w_i / $h_i);
		if (!$w_o) $w_o = $h_o / ($h_i / $w_i);

		$img_o = imagecreatetruecolor($w_o, $h_o);
		imagecopyresampled($img_o, $img, 0, 0, 0, 0, $w_o, $h_o, $w_i, $h_i);
		return imagewebp($img_o, $file_output, 100);

	}

	/**
	 * @param $file_input
	 * @param $file_output
	 * @param string $crop
	 * @param bool $percent
	 * @param bool $avg_bg
	 * @return bool
	 */
	public static function crop($file_input, $file_output, string $crop = 'square', bool $percent = false, bool $avg_bg = false) {

		list($w_i, $h_i, $type) = getimagesize($file_input);
		if (!$w_i || !$h_i) {
			return false;
		}

		$ext = self::types[$type];
		if ($ext) {
			$func = 'imagecreatefrom' . $ext;
			$img = $func($file_input);
		} else {
			return false;
		}

		if ($crop == 'square') {
			$min = $w_i;
			if ($w_i > $h_i) $min = $h_i;
			$w_o = $h_o = $min;
			$x_o = $y_o = 0;
		} else {
			list($x_o, $y_o, $w_o, $h_o) = $crop;
			if ($percent) {
				$w_o *= $w_i / 100;
				$h_o *= $h_i / 100;
				$x_o *= $w_i / 100;
				$y_o *= $h_i / 100;
			}
			if ($w_o < 0) $w_o += $w_i;
			$w_o -= $x_o;
			if ($h_o < 0) $h_o += $h_i;
			$h_o -= $y_o;
		}

		$img_o = imagecreatetruecolor($w_o, $h_o);
		if($avg_bg) {
			$avg_color = ColorThief::getColor($img);
			$bg_color = imagecolorallocate($img_o, $avg_color[0], $avg_color[1], $avg_color[2]);
		} else {
			$bg_color = 000000;
		}
		imagefill($img_o, 0, 0, $bg_color);
		imagecolortransparent($img, 000000);
		imagecopymerge($img_o, $img, 0, 0, $x_o, $y_o, $w_o, $h_o, 100);
		return imagewebp($img_o, $file_output, 100);

	}

}