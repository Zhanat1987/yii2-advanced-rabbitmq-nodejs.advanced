<?php

namespace common\components;

/**
 * @revision      $Id: class.images.helper.php 335 2011-08-08 10:49:11Z agbiggora@gmail.com $
 * @created       Jul 22, 2011
 * @package       Images
 * @subpackage	  Helpers
 * @category      Auxiliary
 * @version       1.0.4
 * @desc          Basic manipulation with image
 * @copyright     Copyright Alexey Gordeyev IK © 2009-2011 - All rights reserved.
 * @license       GPLv2
 * @author        Alexey Gordeyev IK <aleksej@gordejev.lv>
 * @link          http://www.gordejev.lv/
 * @link          http://www.agjoomla.com/
 * @source        http://code.google.com/p/ag-php-classes/wiki/ImagesHelper
 */
Class ImagesHelper
{
    /**
     * Convert color from hex string to rgb color
     * @access public
     * @param  string $color - color in hex format
     * @return array  rgb color as array
     */
    public static function hextorgb($color)
    {
        if (is_array($color)) return $color;
        $color = str_replace('#', '', $color);
        $s = strlen($color) / 3;
        $rgb[] = hexdec(str_repeat(substr($color, 0, $s), 2 / $s));
        $rgb[] = hexdec(str_repeat(substr($color, $s, $s), 2 / $s));
        $rgb[] = hexdec(str_repeat(substr($color, 2 * $s, $s), 2 / $s));
        return $rgb;
    }

    /**
     * Check if GD extension is loaded
     * @access public
     * @return bool
     */
    static public function isgdloaded()
    {
        if (!extension_loaded('gd') && !extension_loaded('gd2')) {
            trigger_error("GD is not loaded", E_USER_WARNING);
            return false;
        }
        else {
            return true;
        }
    }

    /**
     * Check if cURL extension is loaded
     * @access public
     * @return bool
     */
    static public function iscurlloaded()
    {
        if (!extension_loaded('curl')) {
            trigger_error("cURL is not loaded", E_USER_WARNING);
            return false;
        }
        else {
            return true;
        }
    }

    /**
     *
     * Create image from source
     * @access public
     * @param  string $image_source - path to image source
     * @return mixed  gd resource or false
     */
    public static function create($image_source)
    {
        $image = false;
        if(self::isgdloaded()) {
            if(self::isgdresource($image_source)) {
                $image = $image_source;
            } else {
                if(file_exists($image_source)) {
                    $image_type = self::info($image_source,'type');

                    if($image_type) {
                        switch ($image_type)
                        {
                            case 1:
                            case IMAGETYPE_GIF:
                                $image = imagecreatefromgif($image_source);
                                break;
                            case 2:
                            case IMAGETYPE_JPEG:
                                $image = imagecreatefromjpeg($image_source);
                                break;
                            case 3:
                            case IMAGETYPE_PNG:
                                $image = imagecreatefrompng($image_source);
                                break;
                            case 15:
                            case IMAGETYPE_WBMP:
                                $image = imagecreatefromwbmp($image_source);
                                break;
                            case 16:
                            case IMAGETYPE_XBM:
                                $image = imagecreatefromxbm($image_source);
                                break;
                        }
                    }
                }
            }
        }
        return $image;
    }

    /**
     *
     * Validate gd resource
     * @access public
     * @param  mixed $image_source  - gd image resource
     * @return bool
     */
    public static function isgdresource($image_source)
    {
        $gd_resource = false;
        if(gettype($image_source) == 'resource') {
            if(get_resource_type($image_source) == 'gd') {
                $gd_resource = true;
            }
        }
        return $gd_resource;
    }

    /**
     * Convert color image to grayscale
     * @access public
     * @param  string $image_source - path to image source
     * @return mixed  gd resource or false
     */
    public static function grayscale($image_source)
    {
        $image = false;
        if(file_exists($image_source) || self::isgdresource($image_source)) {
            $image = self::create($image_source);
            imagefilter($image,IMG_FILTER_GRAYSCALE);
        }
        return $image;
    }

    /**
     * Conver image to negative
     * @access public
     * @param  string $image_source - path to image source
     * @return mixed  gd resource or false
     */
    public static function negative($image_source)
    {
        $image = false;
        if(file_exists($image_source) || self::isgdresource($image_source)) {
            $image = self::create($image_source);
            imagefilter($image,IMG_FILTER_NEGATE);
        }
        return $image;
    }

    /**
     * Convert image to another format
     * @access public
     * @param  string $image_source - path to image source
     * @param  string $format       - new image format
     * @param  string $prefix       - prefix to the file name
     * @param  string $new_filename - new image file name
     * @return string full path to destination
     */
    public static function convert($image_source,$format = 'png',$prefix = '',$new_filename = false)
    {
        $image = self::create($image_source);
        if($new_filename) {
            $destination = $new_filename;
        }
        else {
            $destination = $image_source;
        }
        return self::save($image,$destination,$prefix,$format);
    }

    /**
     * Output image in to browser
     * @access public
     * @param  object $image   - source image as object
     * @param  string $type    - output image format (default = png)
     * @param  int    $quality - resulted image quality in % (default = 100)
     * @return void
     */
    public static function show($image,$type='png',$quality=100)
    {
        $type = strtolower($type);
        $type = ($type == 'jpg')?'jpeg':$type;
        $type = ($type == 'wbmp')?'vnd.wap.wbmp':$type;
        header('Content-type: image/'.$type);
        // header('Content-Length: ' . strlen($image));
        $type = str_replace('mime/','',$type);

        switch ($type)
        {
            case 'gif':
                imagegif($image);
                break;
            case 'jpg':
            case 'jpeg':
                imagejpeg($image, '',$quality);
                break;
            case 'png':
                imagepng($image);
                break;
            case 'vnd.wap.wbmp':
                imagewbmp($image);
                break;
            case 'xbm':
                imagexbm($image);
                break;
            default:
                echo 'Unsupported image file format.';
        }
        imagedestroy($image);
    }

    /**
     * Save image in to file
     * @access public
     * @param  resource $image       - gd image resource
     * @param  string   $destination - output destination path and filename
     * @param  string   $prefix      - prefix to the file name
     * @param  string   $type        - new image format (default = png)
     * @param  int      $quality     - resulted image quality (default = 100)
     * @return string path to destination
     */
    public static function save($image,$destination,$prefix='',$type='png',$quality=100)
    {
        $type = strtolower($type);

        # build new image name
        if($prefix || $type) {
            if($type != '' || $prefix != '') {
                $destination = self::buildimagename($destination,$prefix,$type);
            }
        }
        if(self::isgdresource($image)) {
            switch ($type)
            {
                case 'gif':
                    imagegif($image,$destination);
                    break;
                case 'jpg':
                case 'jpeg':
                    imagejpeg($image, $destination,$quality);
                    break;
                case 'png':
                    imagepng($image,$destination);
                    break;
                case 'wbmp':
                    imagewbmp($image,$destination);
                    break;
                case 'xbm':
                    imagexbm($image,$destination);
                    break;
                default:
                    echo 'Unsupported image file format.';
            }
        }
        else {
            $destination = false;
        }
        return $destination;
    }

    /**
     * Destroy image resource
     * @access public
     * @param  resource $image  - gd resource
     * @return bool
     */
    public static function destroy($image)
    {
        if(self::isgdresource($image)) {
            imagedestroy($image);
            return true;
        }
        return false;
    }

    /**
     * Get basic information from Image source
     * @access public
     * @param  mixed  $image_source - path to image source or image object
     * @param  string $info_type    - type of the returned information
     * @return mixed  source image information (int, string or array)
     */
    public static function info($image_source,$info_type=false)
    {
        # read information from image file
        if(@is_string($image_source) && @file_exists($image_source)) {
            $image_info   = getimagesize($image_source);
        }
        elseif(self::isgdresource($image_source)) {
            $image_info    = array();
            $image_info[0] = imagesx($image_source);
            $image_info[1] = imagesy($image_source);
            if (imagetypes() & IMG_PNG) {
                $image_info[2] = 3;
                $image_info['mime'] = 'image/png';
            }
            elseif (imagetypes() & IMG_GIF) {
                $image_info[2] = 1;
                $image_info['mime'] = 'image/gif';
            }
            else {
                $image_info[2] = 2;
                $image_info['mime'] = 'image/jpeg';
            }
        }
        else { $image_info = array(0=>0,1=>0,2=>false,'mime'=>false); }

        if($image_info) {
            if($info_type) {
                switch($info_type) {
                    case 'width':
                        return $image_info[0];
                        break;
                    case 'height':
                        return $image_info[1];
                        break;
                    case 'type':
                        return $image_info[2];
                        break;
                    case 'mime':
                        return $image_info['mime'];
                        break;
                    default;
                        return $image_info;
                }
            } else { return $image_info; }
        } else { return false; }
    }

    /**
     * Get image width
     * @access public
     * @param  mixed  $image_source - path to image source or gd resource
     * @return int    source image width in px
     */
    public static function width($image_source)
    {
        return self::info($image_source,'width');
    }

    /**
     * Get image height
     * @access public
     * @param  mixed  $image_source - path to image source or gd resource
     * @return int    source image width in px
     */
    public static function height($image_source)
    {
        return self::info($image_source,'height');
    }

    /**
     * Get image mime/type
     * @access public
     * @param  mixed  $image_source - path to image source or gd resource
     * @return string source image mime type
     */
    public static function mime($image_source)
    {
        return self::info($image_source,'mime');
    }

    /**
     * Clear (normalize) image filename
     * @access public
     * @param  string $filename  - source image filename
     * @return string cleared filename
     */
    public static function clearimagename($filename)
    {
        $imagename = trim($filename);
        $imagename = strtolower($string);
        $imagename = trim(ereg_replace("[^ A-Za-z0-9_]", " ", $imagename));
        $imagename = ereg_replace("[ \t\n\r]+", "_", $imagename);
        $imagename = str_replace(" ", '_', $imagename);
        $imagename = ereg_replace("[ _]+", "_", $imagename);

        return $imagename;
    }

    /**
     * Get image filename
     * @access public
     * @param  string $image_source  - source image filename
     * @return string image file name
     */
    public static function filename($image_source)
    {
        $path_parts = pathinfo($image_source);
        $filename  = $path_parts['filename'];
        $filename .= '.'.$path_parts['extension'];
        return $filename;
    }

    /**
     * Build new image filename name with prefix
     * @access public
     * @param  string $old_filename - source image filename
     * @param  string $prefix       - filename prefix
     * @return string new image filename
     */
    public static function buildimagename($old_filename,$prefix,$type=false)
    {
        $path_parts = pathinfo($old_filename);
        $ext = ($type && $type != '')?$type:$path_parts['extension'];
        $slash = '/';
        $new_filename  = $path_parts['dirname'].$slash;
        $new_filename .= $prefix.$path_parts['filename'];
        $new_filename .= '.'.$ext;
        return $new_filename;
    }

    /**
     * Proportional resize image
     * @access public
     * @param  mixed    $image_source - path to image source or gd image resource
     * @param  int      $new_size     - new image size in px
     * @param  string   $dimension    - resize directions
     * @return resource gd resource
     */
    public static function resizeto($image_source,$new_size,$dimension = "width",$bg_color='#000')
    {
        if(self::isgdloaded()) {
            $width_src    = self::width($image_source);
            $height_src   = self::height($image_source);
            $image_source = self::create($image_source);
            if($dimension == 'width') {
                $ratio = $width_src/$new_size;
            }
            elseif($dimension == 'height') {
                $ratio = $height_src/$new_size;
            }
            $width_dest  = round($width_src/$ratio);
            $height_dest = round($height_src/$ratio);

            return self::resize($image_source,$width_dest,$height_dest);
        }
        else {
            return false;
        }
    }

    /**
     * Resize image to fixed dimensions
     * @access public
     * @param  mixed    $image_source - path to image source or gd image resource
     * @param  int      $width        - new image width in px
     * @param  int      $height       - new image height in px
     * @return resource gd resource
     */
    public static function resize($image_source,$width,$height)
    {
        if(self::isgdloaded()) {
            $width_src    = self::width($image_source);
            $height_src   = self::height($image_source);
            $image_source = self::create($image_source);
            $new_image = imagecreatetruecolor($width, $height);

            // Make the background transparent
            $transparent_bg = imagecolorallocate($new_image, 0, 0, 0);
            imagecolortransparent($new_image, $transparent_bg);

            imagecopyresampled($new_image, $image_source, 0, 0, 0, 0, $width, $height, $width_src, $height_src);
            return $new_image;
        }
        else {
            return false;
        }
    }

    /**
     * Scale image size
     * @access public
     * @param  mixed    $image_source - path to image source or gd image resource
     * @param  int      $ratio        - image scaling value in %
     * @return resource gd resource
     */
    public static function scale($image_source,$ratio)
    {
        if(self::isgdloaded()) {
            $width_src    = self::width($image_source);
            $height_src   = self::height($image_source);
            $width_dest   = round($width_src * $ratio/100);
            $height_dest  = round($height_src * $ratio/100);
            return self::resize($image_source,$width_dest,$height_dest);
        }
        else {
            return false;
        }
    }

    /**
     * Build quadrate image
     * @access public
     * @param  mixed    $image_source - path to image source or gd image resource
     * @param  int      $ratio        - resize ratio
     * @param  string   $dimension    - ration dimension % or px
     * @return resource gd resource
     */
    public static function quadrate($image_source,$ratio,$dimension='px')
    {
        if(self::isgdloaded()) {
            $width_src    = self::width($image_source);
            $height_src   = self::height($image_source);
            $image_source = self::create($image_source);
            if($dimension == 'px') {
                $width_dest   = $ratio;
            } else {
                $width_dest   = round($width_src * $ratio/100);
            }

            $destination_image = imagecreatetruecolor($width_dest,$width_dest);

            if ($width_src>$height_src) {
                imagecopyresized($destination_image,$image_source, 0, 0,
                    round((max($width_src,$height_src)-min($width_src,$height_src))/2),
                    0, $width_dest, $width_dest, min($width_src,$height_src), min($width_src,$height_src));
            }

            if ($width_src<$height_src) {
                imagecopyresized($destination_image,$image_source, 0, 0, 0, 0, $width_dest, $width_dest,
                    min($width_src,$height_src), min($width_src,$height_src));
            }

            if ($width_src==$height_src) {
                imagecopyresized($destination_image,$image_source, 0, 0, 0, 0, $width_dest, $width_dest, $width_src, $width_src);
            }

            return $destination_image;
        }
        else {
            return false;
        }
    }

    /**
     * Build image reflection
     * @access public
     * @param  mixed    $image_source - path to image source or gd image resource
     * @param  int      $ratio        - reflection ratio %
     * @return resource gd resource
     */
    public static function onlyreflection($image_source,$ratio=30)
    {
        if(self::isgdloaded()) {
            $width_src    = self::width($image_source);
            $height_src   = self::height($image_source);
            $image_source = self::create($image_source);

            # calculate reflection height
            $reflection_height = round($height_src * ($ratio/100));

            #	We'll store the final reflection in $output. $buffer is for internal use.
            $output = imagecreatetruecolor($width_src, $reflection_height);
            $buffer = imagecreatetruecolor($width_src, $reflection_height);

            #	Copy the bottom-most part of the source image into the output
            imagecopy($output, $image_source, 0, 0, 0, $height_src - $reflection_height,$width_src, $reflection_height);

            #	Rotate and flip it (strip flip method)
            for ($y = 0; $y < $reflection_height; $y++)
            {
                imagecopy($buffer, $output, 0, $y, 0, $reflection_height - $y - 1, $width_src, 1);
            }

            imagedestroy($output);
            return $buffer;
        }
        else {
            return false;
        }
    }

    /**
     * Build image with reflection
     * @access public
     * @param  mixed    $image_source - path to image source or image object
     * @param  int      $ratio        - reflection ratio %
     * @return resource gd resource
     */
    public static function reflection($image_source,$ratio = 30)
    {
        if(self::isgdloaded()) {
            $width_src    = self::width($image_source);
            $height_src   = self::height($image_source);
            $image_source = self::create($image_source);

            # calculate reflection height
            $reflection_height = round($height_src * ($ratio/100));

            $reflected = imagecreatetruecolor($width_src,$height_src+$reflection_height);
            imagealphablending($reflected, false);
            imagesavealpha($reflected, true);

            imagecopy($reflected, $image_source, 0, 0, 0, 0, $width_src, $height_src);

            $alpha_step = 80 / $reflection_height;
            for ($y = 1; $y <= $reflection_height; $y++) {
                for ($x = 0; $x < $width_src; $x++) {
                    # copy pixel from x / $src_height - y to x / $src_height + y
                    $rgb_color = imagecolorat($image_source, $x, $height_src - $y);

                    $alpha = ($rgb_color & 0x7F000000) >> 24;
                    $alpha =  max($alpha, 47 + ($y * $alpha_step));
                    $rgb_color = imagecolorsforindex($image_source, $rgb_color);
                    $rgb_color = imagecolorallocatealpha($reflected, $rgb_color['red'], $rgb_color['green'], $rgb_color['blue'], $alpha);
                    imagesetpixel($reflected, $x, $height_src + $y - 1, $rgb_color);
                }
            }

            return $reflected;
        }
        else {
            return false;
        }
    }

    /**
     * Fade image
     * @access public
     * @param  mixed    $image_source - path to image source or gd image resource
     * @param  int      $alpha_start  - start alpha level
     * @param  int      $alpha_end    - end alpha level
     * @param  string   $bg_color     - color in hex string format
     * @return resource gd resource
     */
    public static function fade($image_source,$alpha_start,$alpha_end,$bg_color = "#fff")
    {
        if(self::isgdloaded()) {
            if ($alpha_start < 1 || $alpha_start > 127) { $alpha_start = 80; }
            if ($alpha_end < 1 || $alpha_end > 0)	{ $alpha_end = 0; }
            $alpha_length = abs($alpha_start - $alpha_end);
            $rgb_color    = self::hextorgb($bg_color);
            $width_src    = self::width($image_source);
            $height_src   = self::height($image_source);
            $faded        = self::create($image_source);
            imagelayereffect($faded, IMG_EFFECT_OVERLAY);

            for ($y = 0; $y <= $height_src; $y++)
            {
                # Get % of image height
                $pct = $y / $height_src;

                # Get % of alpha
                if ($alpha_start > $alpha_end) {
                    $alpha = (int) ($alpha_start - ($pct * $alpha_length));
                } else {
                    $alpha = (int) ($alpha_start + ($pct * $alpha_length));
                }
                # Rejig it because of the way in which the image effect overlay works
                $final_alpha = 127 - $alpha;
                $allocated_color = imagecolorallocatealpha($faded, $rgb_color[0], $rgb_color[1], $rgb_color[2], $final_alpha);
                imagefilledrectangle($faded, 0, $y, $width_src, $y, $allocated_color);
            }
            return $faded;
        }
        else {
            return false;
        }
    }

    /**
     * Rotate image
     * @access public
     * @param  mixed    $image_source - path to image source or gd image resource
     * @param  int      $degrees      - rotate angle
     * @param  string   $bg_color     - background color (default empty)
     * @param  bool     $transparent  - transparent background (default = true)
     * @return resource gd resource
     */
    public static function rotate($image_source,$degrees,$bg_color='',$transparent=true)
    {
        if(self::isgdloaded()) {
            $rgb_color = false;
            if($bg_color) {
                if(is_array($bg_color)) {
                    $rgb_color = $bg_color;
                } elseif($bg_color != '') {
                    $rgb_color = self::hextorgb($bg_color);
                }
            }

            $image_source = self::create($image_source);
            $allocated_bg_color = false;
            if(is_array($rgb_color)) {
                $allocated_bg_color = imagecolorallocate($image_source, $rgb_color[0], $rgb_color[1], $rgb_color[2]);
            }
            $final_bg_color = ($allocated_bg_color)?$allocated_bg_color:0;
            $rotated_image = imagerotate($image_source, $degrees, $final_bg_color, 0);

            # build transparent background
            if($transparent) {
                imagecolortransparent($rotated_image, $final_bg_color);
            }

            return $rotated_image;
        }
        else {
            return false;
        }
    }

    /**
     *
     * Build text as image
     * @access public
     * @param  string   $font          - path to ttf font file
     * @param  string   $text_color    - text color if false transparent
     * @param  string   $bg_color      - background color if false transparent
     * @return resource gd resource
     */
    public static function text($text, $font_size, $path_to_font, $font_color='#ffffff', $bg_color='#000000', $shadow=false, $shadow_color='#cccccc')
    {
        if(self::isgdloaded()) {
            $bbox = imagettfbbox($font_size, 0,$path_to_font, $text);
            $width = abs($bbox[2] - $bbox[0]);
            $height = abs($bbox[7] - $bbox[1]);

            if(!$bg_color) {
                if($font_color == '#000' || $font_color == '#000000') {
                    $rgb_bg_color = array(0 => 255,1 => 255,2 => 255);
                }
                else {
                    $rgb_bg_color = array(0 => 0,1 => 0,2 => 0);
                }
            }
            else {
                $rgb_bg_color      = self::hextorgb($bg_color);
            }

            if(!$font_color) {
                if(strtolower($bg_color) == '#fff' || strtolower($bg_color) == '#ffffff') {
                    $rgb_font_color = array(0 => 0,1 => 0,2 => 0);
                }
                else {
                    $rgb_font_color = array(0 => 255,1 => 255,2 => 255);
                }
            }
            else {
                $rgb_font_color      = self::hextorgb($font_color);
            }

            $text_img = imagecreatetruecolor($width, $height);
            $allocated_bg_color = imagecolorallocate($text_img, $rgb_bg_color[0], $rgb_bg_color[1], $rgb_bg_color[2]);
            $allocated_text_color = imagecolorallocate($text_img, $rgb_font_color[0], $rgb_font_color[1], $rgb_font_color[2]);

            $x = $bbox[0] + ($width / 2) - ($bbox[4] / 2) * 0.9;
            $y = $bbox[1] + ($height / 2) - ($bbox[5] / 2) * 0.9;

            imagefilledrectangle($text_img, 0, 0, $width - 1, $height - 1, $allocated_bg_color);
            imagettftext($text_img, $font_size, 0, $x, $y, $allocated_text_color, $path_to_font, $text);

            $last_pixel= imagecolorat($text_img, 0, 0);

            for ($j = 0; $j < $height; $j++)
            {
                for ($i = 0; $i < $width; $i++)
                {
                    if (isset($blank_left) && $i >= $blank_left) {  break;  }

                    if (imagecolorat($text_img, $i, $j) !== $last_pixel)
                    {
                        if (!isset($blank_top)) {  $blank_top = $j;  }
                        $blank_left = $i;
                        break;
                    }
                    $last_pixel = imagecolorat($text_img, $i, $j);
                }
            }

            $x -= $blank_left;
            $y -= $blank_top;

            imagefilledrectangle($text_img, 0, 0, $width - 1, $height - 1, $allocated_bg_color);
            imagettftext($text_img, $font_size, 0, $x, $y, $allocated_text_color, $path_to_font, $text);
            # build transparent background
            if(!$bg_color) {
                imagecolortransparent($text_img, $allocated_bg_color);
            }
            if(!$font_color) {
                imagecolortransparent($text_img, $allocated_text_color);
            }
            return $text_img;
        }
        else {
            return false;
        }
    }

    /**
     * Enter description here ... textto
     * @access public
     * @param  mixed  $image_source
     * @param  string $text
     * @param  int    $font_size
     * @param  string $path_to_font
     * @param  string $font_color
     * @param  string $bg_color
     * @param  bool   $shadow
     * @param  string $shadow_color
     * @return resource gd resource
     */
    public static function textto( $image_source, $text, $font_size, $path_to_font, $bg_color='#000000')
    {
        if(self::isgdloaded()) {
            $transparent_color = false;
            if(!$bg_color) {
                $transparent_color = '#000000';
            }
            $text_img     = self::text($text, $font_size, $path_to_font, false, $bg_color);
            $crp_width    = self::width($text_img);
            $crp_height   = self::height($text_img);

            if($crp_width > self::width($image_source)) {
                $image_source = self::resizeto($image_source,$crp_width,"width");
            }
            if($crp_height > self::height($image_source)) {
                $image_source = self::resizeto($image_source,$crp_height,"height");
            }

            $src_width    = self::width($image_source);
            $src_height   = self::height($image_source);
            $src_x        = ($src_width - $crp_width) / 2;
            $src_y        = ($src_height - $crp_height) / 2;

            $image_dest = self::crop($image_source, $src_x, $src_y, $crp_width, $crp_height);
            $image_dest = self::overlay($image_dest, $text_img, 'center', 100);
            if($transparent_color) {
                $rgb_color      = self::hextorgb($transparent_color);
                $allocated_color = imagecolorallocate($image_dest, $rgb_color[0], $rgb_color[1], $rgb_color[2]);
                imagecolortransparent($image_dest, $allocated_color);
            }
            return $image_dest;
        }
        else {
            return false;
        }
    }

    /**
     * Build text watermark
     * @access public
     * @param  mixed    $image_source - path to image source or gd image resource
     * @param  string   $text         - text
     * @param  string   $font         - path to ttf font file
     * @param  string   $color        - text color in hex format (default white #fff)
     * @param  string   $position     - text overlay position
     * @param  int      $alpha_level  - text alpha level (default = 30)
     * @param  int      $font_size    - font size (default calculate from image source)
     * @param  int      $angle        - text rotate angle
     * @return resource gd resource
     */
    public static function overlaytext( $image_source, $text, $font, $color = '#ffffff', $position = 'center', $alpha_level = 40, $angle = 0, $font_size = false)
    {
        if(self::isgdloaded()) {
            $width_src    = self::width($image_source);
            $height_src   = self::height($image_source);
            /*
             if(!$angle) {
             $angle     =  -rad2deg(atan2((-$height_src),($width_src)));
             }
             */
            $rgb_color    = self::hextorgb($color);
            $image_source = self::create($image_source);
            $text = " ".$text." ";
            $alpha_level = ($alpha_level > 127)?0:127 - $alpha_level;
            $allocated_bg_color = imagecolorallocatealpha($image_source, $rgb_color[0], $rgb_color[1], $rgb_color[2], $alpha_level);
            $text_box_size = round((($width_src+$height_src) / 2 * 0.9)/strlen($text));

            // $text_box_size = ($text_box_size < $font_size)?$text_box_size:(int)$font_size;
            $text_box  = imagettfbbox($text_box_size, $angle, $font, $text );
            $x = $width_src/2 - abs($text_box[4] - $text_box[0])/2;
            $y = $height_src/2 + abs($text_box[5] - $text_box[1])/2;
            $sample = imagecreatetruecolor($y,$x);
            $coordinates = self::calculateposition($image_source, $sample, $position);
            imagettftext($image_source, $text_box_size, $angle, $x, $y, $allocated_bg_color, $font, $text);

            return $image_source;
        }
        else {
            return false;
        }
    }

    /**
     * Build overlay image from another image
     * @access public
     * @param  mixed    $image_source  - path to image source or gd image resource
     * @param  string   $watermark_img - path to watemark image source
     * @param  int      $alpha_level   - watemark image aplha level (default = 100)
     * @return resource gd resource
     */
    public static function overlay($image_source, $watermark_img, $position = 'random', $alpha_level = 50, $ratio = 0)
    {
        if(self::isgdloaded()) {
            $watermark_img    = self::create($watermark_img);
            $width_src        = self::width($image_source);
            $height_src       = self::height($image_source);
            $image_source     = self::create($image_source);
            $cnt              = ($ratio > 0)?$ratio / 100:1;

            if($width_src < self::width($watermark_img)) {
                $watermark_img  = ImagesHelper::resizeto($watermark_img,round($width_src * $cnt),'width');
            }
            elseif($height_src < self::height($watermark_img)) {
                $watermark_img  = ImagesHelper::resizeto($watermark_img,round($height_src * $cnt),'height');
            }

            $watermark_width  = self::width($watermark_img);
            $watermark_height = self::height($watermark_img);

            $coordinates = self::calculateposition($image_source,$watermark_img,$position);
            imagecopymerge($image_source, $watermark_img, $coordinates['dst_x'], $coordinates['dst_y'], 0, 0, $watermark_width, $watermark_height, $alpha_level);

            return $image_source;
        }
        else {
            return false;
        }
    }


    /**
     * Calculate watemark position
     * @access public
     * @param  mixed  $src_image     - path to image source or gd image resource
     * @param  mixed  $watermark_img - path to image destination or gd image resource
     * @param  string $position      - overlay position
     * @return array  start overlay coordinates
     */
    public static function calculateposition($image_source,$watermark_img,$position)
    {
        if(self::isgdloaded()) {
            $src_w  = self::width($image_source);
            $src_h  = self::height($image_source);
            $wtm_w  = self::width($watermark_img);
            $wtm_h  = self::height($watermark_img);
            $dst_x  = $src_w - $wtm_w;
            $dst_y  = $src_h - $wtm_h;

            $coordinates = array('dst_x'=>0,'dst_y'=>0);

            if ($position == 'random') {
                $position = rand(1,8);
            }

            switch ($position) {
                case 'top-right':
                case 'right-top':
                case 1:
                    $coordinates['dst_x'] = $dst_x-$wtn_w;
                    break;
                case 'top-left':
                case 'left-top':
                case 2:
                    break;
                case 'bottom-right':
                case 'right-bottom':
                case 3:
                    $coordinates['dst_x'] = $src_w-$wtm_w;
                    $coordinates['dst_y'] = $src_h-$wtm_h;
                    break;
                case 'bottom-left':
                case 'left-bottom':
                case 4:
                    $coordinates['dst_y'] = $src_h-$wtm_h;
                    break;
                case 'center':
                case 5:
                    $coordinates['dst_x'] = ($src_w/2)-($wtm_w/2);
                    $coordinates['dst_y'] = ($src_h/2)-($wtm_h/2);
                    break;
                case 'top':
                case 6:
                    $coordinates['dst_x'] = ($src_w/2)-($wtm_w/2);
                    break;
                case 'bottom':
                case 7:
                    $coordinates['dst_x'] = ($src_w/2)-($wtm_w/2);
                    $coordinates['dst_y'] = $src_h-$wtm_h;
                    break;
                case 'left':
                case 8:
                    $coordinates['dst_y'] = ($src_h/2)-($wtm_h/2);
                    break;
                case 'right':
                case 9:
                    $coordinates['dst_x'] = $src_w-$wtm_w;
                    $coordinates['dst_y'] = ($src_h/2)-($wtm_h/2);
                    break;
            }
            return $coordinates;
        }
        else {
            return false;
        }
    }

    /**
     * Draws the gradient image
     * @access public
     * @param  int      $image_width   - image width
     * @param  int      $image_height  - image height
     * @param  string   $direction     - gradient direction
     * @param  string   $start_color   - start color in hex color
     * @param  string   $end_color     - end color in hex color
     * @return resource gd image resource
     */
    public static function gradientfill($image_width,$image_height,$direction,$start_color,$end_color,$step = 0)
    {
        if(self::isgdloaded()) {
            $r = $g = $b = null;
            list($r1,$g1,$b1) = self::hextorgb($end_color);
            list($r2,$g2,$b2) = self::hextorgb($start_color);
            $gradiened        = imagecreatetruecolor($image_width,$image_height);
            $center_x         = $image_width/2;
            $center_y         = $image_height/2;

            switch($direction) {
                case 'horizontal':
                case 'vertical':
                    $line_numbers = ($direction == 'vertical')?imagesy($gradiened):imagesx($gradiened);
                    $line_width = ($direction == 'vertical')?imagesx($gradiened):imagesy($gradiened);
                    list($r1,$g1,$b1) = self::hextorgb($start_color);
                    list($r2,$g2,$b2) = self::hextorgb($end_color);
                    break;
                case 'ellipse':
                    $rh=$image_height>$image_width?1:$image_width/$image_height;
                    $rw=$image_width>$image_height?1:$image_height/$image_width;
                    $line_numbers = min($image_width,$image_height);
                    imagefill($gradiened, 0, 0, imagecolorallocate($gradiened, $r1, $g1, $b1 ));
                    break;
                case 'ellipse2':
                    $rh=$image_height>$image_width?1:$image_width/$image_height;
                    $rw=$image_width>$image_height?1:$image_height/$image_width;
                    $line_numbers = sqrt(pow($image_width,2)+pow($image_height,2));
                    break;
                case 'circle':
                    $line_numbers = min($image_width,$image_height);
                    $rh = $rw = 1;
                    imagefill($gradiened, 0, 0, imagecolorallocate($gradiened, $r1, $g1, $b1));
                    break;
                case 'circle2':
                    $line_numbers = sqrt(pow($image_width,2)+pow($image_height,2));
                    $rh = $rw = 1;
                    break;
                case 'square':
                case 'rectangle':
                    $line_numbers = max($image_width,$image_height)/2;
                    break;
                case 'diamond':
                    $rh=$image_height>$image_width?1:$image_width/$image_height;
                    $rw=$image_width>$image_height?1:$image_height/$image_width;
                    $line_numbers = round(min($image_width,$image_height)*0.95);
                    break;
                default:
            }

            for ( $i = 0; $i < $line_numbers; $i=$i+1+$step ) {
                // old values :
                $old_r=$r;
                $old_g=$g;
                $old_b=$b;
                // new values :
                $r = ( $r2 - $r1 != 0 ) ? intval( $r1 + ( $r2 - $r1 ) * ( $i / $line_numbers ) ): $r1;
                $g = ( $g2 - $g1 != 0 ) ? intval( $g1 + ( $g2 - $g1 ) * ( $i / $line_numbers ) ): $g1;
                $b = ( $b2 - $b1 != 0 ) ? intval( $b1 + ( $b2 - $b1 ) * ( $i / $line_numbers ) ): $b1;

                if ( "$old_r,$old_g,$old_b" != "$r,$g,$b") { $fill = imagecolorallocate($gradiened, $r, $g, $b ); }
                switch($direction) {
                    case 'vertical':
                        imagefilledrectangle($gradiened, 0, $i, $line_width, $i+$step, $fill);
                        break;
                    case 'horizontal':
                        imagefilledrectangle($gradiened, $i, 0, $i+$step, $line_width, $fill );
                        break;
                    case 'ellipse':
                    case 'ellipse2':
                    case 'circle':
                    case 'circle2':
                        imagefilledellipse ($gradiened,$center_x, $center_y, ($line_numbers-$i)*$rh, ($line_numbers-$i)*$rw,$fill);
                        break;
                    case 'square':
                    case 'rectangle':
                        imagefilledrectangle ($gradiened,$i*$image_width/$image_height,$i*$image_height/$image_width,$image_width-($i*$image_width/$image_height), $image_height-($i*$image_height/$image_width),$fill);
                        break;
                    case 'diamond':
                        imagefilledpolygon($gradiened, array (
                            $image_width/2, $i*$rw-0.5*$image_height,
                            $i*$rh-0.5*$image_width, $image_height/2,
                            $image_width/2,1.5*$image_height-$i*$rw,
                            1.5*$image_width-$i*$rh, $image_height/2 ), 4, $fill);
                        break;
                    default:
                }
            }
            return $gradiened;
        }
        else {
            return false;
        }
    }

    /**
     *
     * Set tranparent color
     * @access public
     * @param  mixed    $image - path to image source or gd image resource
     * @param  mixed    $color - color hex string format or rgb array
     * @return resource gd resource
     */
    public static function tranparent($image_source,$color)
    {
        if(self::isgdloaded()) {
            $rgb_color       = false;
            $image_source    = self::create($image_source);
            if($color) {
                if(is_array($color)) {
                    $rgb_color = $color;
                } elseif($color != '') {
                    $rgb_color = self::hextorgb($color);
                }
            }
            if(is_array($rgb_color)) {
                $allocated_bg_color = imagecolorallocate($image_source, $rgb_color[0], $rgb_color[1], $rgb_color[2]);
            }
            # build transparent background
            if($allocated_bg_color) {
                imagecolortransparent($image_source, $allocated_bg_color);
            }
            return $image_source;
        }
        else {
            return false;
        }
    }

    /**
     * Crop image
     * @access public
     * @param  mixed    $image_source - path to image source or gd image resource
     * @param  int      $src_x        - x-coordinate of source point.
     * @param  int      $src_y        - y-coordinate of source point.
     * @param  int      $crp_width    - destination image width
     * @param  int      $crp_height   - destination image height
     * @return resource gd resource
     */
    public static function crop($image_source, $src_x, $src_y, $crp_width, $crp_height)
    {
        if(self::isgdloaded()) {
            $width_src        = self::width($image_source);
            $height_src       = self::height($image_source);
            $image_source     = self::create($image_source);
            $crp_width        = ($crp_width >= $width_src || $crp_width <= 0)?$width_src:$crp_width;
            $crp_height       = ($crp_height >= $height_src || $crp_height <= 0)?$height_src:$crp_height;
            $destination = imagecreatetruecolor($crp_width, $crp_height);
            imagecopy($destination, $image_source, 0, 0, $src_x, $src_y, $crp_width, $crp_height);
            imagedestroy($image_source);
            return $destination;
        }
        else {
            return false;
        }
    }

    /**
     * Download image from remote server
     * @access public
     * @param  string $path_to_source - path to remote image source
     * @param  mixed  $path_to_temp   - path to local destination
     * @return string full local path to downloaded image
     */
    public function download($path_to_source,$path_to_temp = false)
    {
        if(self::iscurlloaded()) {
            $filename = false;
            if(!$path_to_temp) {
                $path_to_temp = sys_get_temp_dir();
            }
            if($path_to_source && mb_substr($path_to_source, 0, 7) == 'http://') {
                $filename = self::filename($path_to_source);
                $path_to_temp = $path_to_temp.$filename;
            }
            if($filename && !file_exists($path_to_temp)) {
                # cURL initialization
                $ch = curl_init();
                curl_setopt($ch,CURLOPT_URL,$path_to_source);
                curl_setopt($ch,CURLOPT_FAILONERROR, 1);
                curl_setopt($ch,CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch,CURLOPT_AUTOREFERER, 1);
                curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
                # set cURL connection timeout
                curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,500);
                # execute cURL
                $image_source = curl_exec($ch);
                # write image to temporary folder
                file_put_contents($path_to_temp,$image_source);
                # close cURL
                curl_close($ch);
                return $path_to_temp;
            }
            elseif(file_exists($path_to_temp)) {
                return $path_to_temp;
            }
            else {
                return $path_to_source;
            }
        }
        else {
            return false;
        }
    }

}