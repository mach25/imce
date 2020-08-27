<?php

namespace Drupal\imce;

/**
 * Image compress trait.
 */
trait ImceImageCompressTrait {


  public static function compressTinify($uri) {
    \Tinify\setkey(\Drupal::config('imce.settings')->get('tinify_api_key'));
    \Tinify\fromFile($uri)->toFile($uri);
  }

  public function compressGd($uri) {
    $info = getimagesize($uri);

    if ($info['mime'] == 'image/jpeg') {
      $image = imagecreatefromjpeg($uri);
    }
    elseif ($info['mime'] == 'image/gif') {
      $image = imagecreatefromgif($uri);
    }
    elseif ($info['mime'] == 'image/png') {
      $image = imagecreatefrompng($uri);
    }
    else {
      die('Unknown image file format');
    }

    // Compress and save file to jpg.
    imagejpeg($image, $uri, \Drupal::config('imce.settings')->get('quality_gd'));

    // Return destination file.
    return $uri;
  }

  public function compressImagick($uri) {

    $imagePath = \Drupal::service('file_system')->realpath($uri);
    $img = new \Imagick();
    $img->readImage($imagePath);
    $img->setImageCompression(\Imagick::COMPRESSION_JPEG2000);
    $img->setImageCompressionQuality(\Drupal::config('imce.settings')->get('quality_imagick'));
    $img->stripImage();
    $img->writeImage($imagePath);
  }

  public function noCompress($uri) {
    return FALSE;
  }


  public function imageCompress($uri) {
    if (!exif_imagetype($uri)) {
      return FALSE;
    }

    $compressType = \Drupal::config('imce.settings')->get('compress_type');
    $this->$compressType($uri);
  }

}
