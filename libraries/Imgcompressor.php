<?php
/******************************************************************************
* #### PHP Image Compressor and Thumbnail Creation Library for CodeIgniter ####
* 
* Modified and Re-Compossed by Bagus We Pe 2017.
* https://bitbucket.org/wepe/image-compress-for-codeigniter
*
******************************************************************************/

/******************************************************************************
* CREDIT FOR:
* 
* PHP Image Compressor Class by Ican Bachors
* http://ibacor.com/labs/php-image-compressor-class
*
* ImageCraft by coldume
* https://github.com/coldume/imagecraft
******************************************************************************/

/******************************************************************************
* HOW TO USE AND SETTING UP AN OPTION:
* 
* Use this setting to setup an create an option for compression images
*   $setting['image_path'] = './media/temp/'; //image uploading path
*   $setting['image_name'] = 'my_image.jpg'; //image name
*   $setting['compress_path'] = './media/'; //image after compressing path
*   $setting['jpg_compress_level'] = 5; //.jpg compression level, from 0 to 9
*   $setting['png_compress_level'] = 5; //.png compression level, from 0 to 9
*   $setting['create_thumb'] = TRUE; //create thumb image or not, TRUE / FALSE
*   $setting['width_thumb'] = 300; //thumb width
*   $setting['height_thumb'] = 300; //thumb height
*
* then call this library and run it 
*   $this->load->library('imgcompressor', $setting); //call the library
*   $result = $this->imgcompressor->do_compress(); //run it
******************************************************************************/

/******************************************************************************
* NOTES:
* 
* This library is optimazed for jpg image
* This library not maximal to compress .png image file size
* This library not supporting to compress .gif file size and quality
******************************************************************************/

use Imagecraft\ImageBuilder;

class Imgcompressor {	
	
	function __construct($setting) {
		$this->setting = $setting;
	}
	
	function do_compress(){
		//allowed file type
		$file_type = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif');

		//get settings for compression
		$img_path = $this->setting['image_path'];
		$img_name = $this->setting['image_name'];
		
		//get file info
		$image = $img_path.$img_name;
		$im_info = getImageSize($image);
		$im_name = basename($image);
		$im_type = $im_info['mime'];
		$im_size = filesize($image);
		
		//result
		$result = array();
		
		//check files
		if (file_exists($image)){
			if (in_array($im_type, $file_type)){
				$result['data'] = $this->create($image, $im_info, $im_name, $im_type, $im_size);
			}
			else{
				$result['status'] = 'error';
				$result['message'] = 'Failed file type';
			}
		}
		else {
			$result['status'] = 'error';
			$result['message'] = 'Image file not found.';
		}			
		
		return $result;
	}
	
	private function create($image, $im_info, $name, $type, $size){

		//load setting data
		$leveljpg = (isset($this->setting['jpg_compress_level']) ? $this->setting['jpg_compress_level'] : 5);
		$levelpng = (isset($this->setting['png_compress_level']) ? $this->setting['png_compress_level'] : 5);
		$thumb = (isset($this->setting['create_thumb']) ? $this->setting['create_thumb'] : FALSE);
		$width_thumb = (isset($this->setting['width_thumb']) ? $this->setting['width_thumb'] : 150);
		$height_thumb = (isset($this->setting['height_thumb']) ? $this->setting['height_thumb'] : 150);

		$im_name = $name;
		$im_det = explode('.', $name);
		$im_output = $this->setting['compress_path'].$im_name;
		$im_ex = explode('.', $im_output); // get file extension
		
		// create image
		if ($type == 'image/jpeg'){

			$im = imagecreatefromjpeg($image); // create image from jpeg

			$im_name = str_replace(end($im_ex), 'jpg', $im_name); // replace file extension
			$im_output = str_replace(end($im_ex), 'jpg', $im_output); // replace file extension

			if(!empty($leveljpg)){
				imagejpeg($im, $im_output, 100 - ($leveljpg * 10)); // if level = 2 then quality = 80%
			}
			else{
				imagejpeg($im, $im_output, 100); // default quality = 100% (no compression)
			}

			$im_type = 'image/jpeg';

		}

		else if ($type == 'image/png'){
			$im = imagecreatefrompng($image);  // create image from png (default)

			$im_name = str_replace(end($im_ex), 'png', $im_name); // replace file extension
			$im_output = str_replace(end($im_ex), 'png', $im_output); // replace file extension

			if ($this->check_transparent($im)){ // Check if image is transparent
				imageAlphaBlending($im, true);
				imageSaveAlpha($im, true);
				imagepng($im, $im_output, $levelpng); // if level = 2 like quality = 80%
			}
			else {
				imagepng($im, $im_output, $levelpng); // default level = 0 (no compression)
			}

			$im_type = 'image/png';
		}
		
		else if ($type == 'image/gif'){
			$im = imagecreatefromgif($image); // create image from gif

			$im_name = str_replace(end($im_ex), 'gif', $im_name); // replace file extension
			$im_output = str_replace(end($im_ex), 'gif', $im_output); // replace file extension

			if ($this->is_animated($image)){
				$imgcraft = $this->load_imagecraft(); //using imagecraft when gif is animated
				$craft = $imgcraft
					->addBackgroundLayer()
					->filename($image)
					->resize($im_info[0], $im_info[1], 'shrink')
					->done()
					->save();
				file_put_contents($this->setting['compress_path'].$im_det[0].'.'.$craft->getExtension(), $craft->getContents());
			}
			else {
				if ($this->check_transparent($im)){
					imageAlphaBlending($im, true);
					imageSaveAlpha($im, true);
					imagegif($im, $im_output);
				}
				else {
					imagegif($im, $im_output);
				}
			}

			$im_type = 'image/gif';
		}

		if ($thumb != null && $thumb == TRUE){
			$im_thumb = $this->setting['compress_path'].'/'.$im_det[0].'_thumb.'.$im_det[1];
			$this->create_thumb($image, $im_info, $im_type, $im_thumb, $im_det[0].'_thumb');
		}
		
		// image destroy
		imagedestroy($im);
		
		// output original image & compressed image
		$im_size = filesize($im_output);
		$im_new = getImageSize($im_output);
		$data = array(
			'original' => array(
				'name' => $name,
				'image' => $image,
				'type' => $type,
				'width' => $im_info[0],
				'height' => $im_info[1],
				'size' => $size
			),
			'compressed' => array(
				'name' => $im_name,
				'image' => $im_output,
				'type' => $im_type,
				'width' => $im_new[0],
				'height' => $im_new[1],
				'size' => $im_size
			)
		);

		if ($thumb != null && $thumb == TRUE){
			$thumb_info = getImageSize($im_thumb);
			$data['thumbnail'] = array(
				'name' => $im_det[0].'_thumb.'.$im_det[1],
				'image' => $im_thumb,
				'type' => $im_type,
				'width' => $thumb_info[0],
				'height' => $thumb_info[1],
				'size' => filesize($im_thumb)
			);
		}

		return $data;
	}

	private function create_thumb($image, $im_info, $im_type, $out_filename, $filename){

		//load setting data
		$leveljpg = (isset($this->setting['jpg_compress_level']) ? $this->setting['jpg_compress_level'] : 5);
		$levelpng = (isset($this->setting['png_compress_level']) ? $this->setting['png_compress_level'] : 5);
		$thumb = (isset($this->setting['create_thumb']) ? $this->setting['create_thumb'] : FALSE);
		$width_thumb = (isset($this->setting['width_thumb']) ? $this->setting['width_thumb'] : 150);
		$height_thumb = (isset($this->setting['height_thumb']) ? $this->setting['height_thumb'] : 150);

		$forcedWidth = $width_thumb;
		$forcedHeight = $height_thumb;
		$sourceSize = $im_info;
	
		// For a landscape picture or a square
		if ($sourceSize[0] >= $sourceSize[1]){
			$finalWidth = $forcedWidth;
			$finalHeight = ($forcedWidth / $sourceSize[0]) * $sourceSize[1];
		}
		// For a potrait picture
		else {
			$finalWidth = ($forcedHeight / $sourceSize[1]) * $sourceSize[0];
			$finalHeight = $forcedHeight;
		}

		if ($im_type == 'image/jpeg'){
			$sourceID = imagecreatefromjpeg($image);
			$targetID = imagecreatetruecolor($finalWidth, $finalHeight);
			imagecopyresampled($targetID, $sourceID, 0, 0, 0, 0, $finalWidth, $finalHeight, $sourceSize[0], $sourceSize[1]);
			imagejpeg($targetID, $out_filename, 100 - ($leveljpg * 10));
			imagedestroy($sourceID);
			imagedestroy($targetID);
		}

		else if ($im_type == 'image/png'){
			$sourceID = imagecreatefrompng($image);
			$targetID = imagecreatetruecolor($finalWidth, $finalHeight);

			if ($this->check_transparent($sourceID)){
				imageAlphaBlending($targetID, true);
				$alpha = imagecolorallocatealpha($targetID, 0, 0, 0, 127);
				imagefill($targetID, 0, 0, $alpha);
				imageSaveAlpha($targetID, true);
				imagecopyresampled($targetID, $sourceID, 0, 0, 0, 0, $finalWidth, $finalHeight, $sourceSize[0], $sourceSize[1]);
				imagepng($targetID, $out_filename, $levelpng);
			}
			else {
				imagecopyresampled($targetID, $sourceID, 0, 0, 0, 0, $finalWidth, $finalHeight, $sourceSize[0], $sourceSize[1]);
				imagepng($targetID, $out_filename, $levelpng);
			}
			imagedestroy($sourceID);
			imagedestroy($targetID);
		}

		else if ($im_type == 'image/gif'){ //using imagecraft when gif files
			$imgcraft = $this->load_imagecraft();
			$craft = $imgcraft
				->addBackgroundLayer()
				->filename($image)
				->resize($finalWidth, $finalHeight, 'shrink')
				->done()
				->save();
			file_put_contents($this->setting['compress_path'].$filename.'.'.$craft->getExtension(), $craft->getContents());
		}
	}

	private function check_transparent($im) {

		$width = imagesx($im); // Get the width of the image
		$height = imagesy($im); // Get the height of the image

		// We run the image pixel by pixel and as soon as we find a transparent pixel we stop and return true.
		for($i = 0; $i < $width; $i++) {
			for($j = 0; $j < $height; $j++) {
				$rgba = imagecolorat($im, $i, $j);
				if(($rgba & 0x7F000000) >> 24) {
					return true;
				}
			}
		}

		// If we dont find any pixel the function will return false.
		return false;
	}

	private function is_animated($image){
		$filecontents = file_get_contents($image);

		$str_loc = 0;
		$count = 0;
		while ($count < 2){ # There is no point in continuing after we find a 2nd frame
			$where1 = strpos($filecontents,"\x00\x21\xF9\x04",$str_loc);
			if ($where1 === FALSE){
				break;
			}
			else {
				$str_loc = $where1 + 1;
				$where2 = strpos($filecontents,"\x00\x2C",$str_loc);
				if ($where2 === FALSE){
					break;
				}
				else {
					if ($where1 + 8 == $where2){
						$count++;
					}
					$str_loc=$where2+1;
				}
			}
		}

		if ($count > 1){
			return(true);
		}
		else {
			return(false);
		}
	}

	private function load_imagecraft(){
		include_once APPPATH.'/third_party/imgcompressor/autoload.php';
		$options = ['engine'=>'php_gd', 'locale'=>'en'];
		$load = new ImageBuilder($options);
		return $load;
	}
}
