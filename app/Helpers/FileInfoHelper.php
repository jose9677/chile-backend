<?php 

namespace App\Helpers;

/**
 * @see https://stackoverflow.com/a/45054843
 */
class FileInfoHelper
{

    static function get_image_ext($image_mime)
    {
        $mimes  = array(
            "image/gif" => "gif",
            "image/jpg" => "jpg",
            "image/jpeg" => "jpg",
            "image/png" => "png",
            "image/swf" => "swf",
            "image/psd" => "psd",
            "image/bmp" => "bmp",
            "image/tiff" => "tiff",
            "image/jpc" => "jpc",
            "image/jp2" => "jp2",
            "image/jpx" => "jpx",
            "image/jb2" => "jb2",
            "image/swc" => "swc",
            "image/iff" => "iff",
            "image/wbmp" => "wbmp",
            "image/xbm" => "xbm",
            "image/ico" => "ico",
            "application/pdf" => "pdf"
        );
    
        /*
        if (($image_type = exif_imagetype($image_mime))
            && (array_key_exists($image_type ,$mimes)))
        {
            return $mimes[$image_type];
        }
        else
        {
            return FALSE;
        }
        */
        if ($ext = array_key_exists($image_mime ,$mimes))
            return $mimes[$image_mime];
        else
            return false;
    }



}