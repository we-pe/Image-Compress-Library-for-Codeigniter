# **Image Compress for CodeIgniter** #

## **What is this repository for?** ##

This is an Image Compression Library for CodeIgniter. This library compressing image to smaller size but not reduce the image quality. This library supporting an .gif animation image and created the thumbnail.


## **Requirement** ##

*   PHP >= 5.4.0
*   PHP GD extension
*   CodeIgniter > 3.0.0


## **How do I get set up ?** ##

**Just copy and paste all files on your codeigniter application folder**


## **How do I use ?** ##

Provide the User with a set up this parameters on controller file
````php
$setting['image_path'] = './media/temp/';
$setting['image_name'] = 'my_image.jpg';
$setting['compress_path'] = './media/';
$setting['jpg_compress_level'] = 5;
$setting['png_compress_level'] = 5;
$setting['create_thumb'] = TRUE;
$setting['width_thumb'] = 300;
$setting['height_thumb'] = 300;
````

After the User set up all parameters, run it
````php
$this->load->library('imgcompressor', $setting);
$result = $this->imgcompressor->do_compress();
````


## **Cheat Sheet / Option** ##

| Parameter            | Default   | Available       | Description                           |
| :------------------- | :-------- | :-------------- | :------------------------------------ |
| `image_path`         | `n/a`     | `n/a`           | image uploading/source path           |
| `image_name`         | `n/a`     | `n/a`           | image name                            |
| `compress_path`      | `n/a`     | `n/a`           | image after compressing path          |
| `jpg_compress_level` | `5`       | `[0, 5]`        | jpg compression level                 |
| `png_compress_level` | `5`       | `[0, 5]`        | png compression level                 |
| `create_thumb`       | `false`   | `true, false`   | create thumb image or not             |
| `width_thumb`        | `150`     | `n/a` (numeric) | thumb width                           |
| `height_thumb`       | `150`     | `n/a` (numeric) | thumb height                          |


## **Output** ##

````php
$data = array(
	'original' => array(
		'name' => 'original.jpg',
		'image' => './media/temp/original.jpg',
		'type' => 'jpg,
		'width' => '1600',
		'height' => '1200',
		'size' => 925488
	),
	'compressed' => array(
		'name' => 'output.jpg',
		'image' => './media/output.jpg',
		'type' => 'jpg,
		'width' => '1600',
		'height' => '1200',
		'size' => 256190
	),
	'thumbnail' => array(
		'name' => 'output_thumb.jpg',
		'image' => './media/output_thumb.jpg',
		'type' => 'jpg,
		'width' => '300',
		'height' => '200',
		'size' => 56190
	)
);
````


## **Resources** ##
* Ican Bachors - "PHP Image Compressor Class".
  http://ibacor.com/labs/php-image-compressor-class.

* Coldume - "ImageCraft".
  https://github.com/coldume/imagecraft.