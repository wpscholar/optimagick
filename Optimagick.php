<?php

namespace wpscholar;

/**
 * Class Optimagick
 *
 * @package wpscholar
 */
class Optimagick {

	/**
	 * @var \Imagick
	 */
	protected $image;

	/**
	 * Check if object is an instance of Imagick
	 *
	 * @param object $image
	 *
	 * @return bool
	 */
	public static function isImagick( $image ) {
		return is_object( $image ) && is_a( $image, '\Imagick' );
	}

	/**
	 * Check if 'imagick' PHP extension is installed
	 *
	 * @return bool
	 */
	public static function isImagickInstalled() {
		return extension_loaded( 'imagick' );
	}

	/**
	 * Optimagick constructor.
	 *
	 * @param \Imagick $image
	 */
	public function __construct( \Imagick $image ) {
		$this->image = $image;
	}

	/**
	 * Check if image is animated
	 *
	 * @return bool
	 */
	public function isAnimated() {
		return $this->image->getNumberImages() > 1;
	}

	/**
	 * Check if image is a GIF
	 *
	 * @return bool
	 */
	public function isGIF() {
		return $this->getFormat() === 'GIF';
	}

	/**
	 * Check if image is a JPEG
	 *
	 * @return bool
	 */
	public function isJPEG() {
		return $this->getFormat() === 'JPEG';
	}

	/**
	 * Check if image is a PNG
	 *
	 * @return bool
	 */
	public function isPNG() {
		return $this->getFormat() === 'PNG';
	}

	/**
	 * Get the image format
	 *
	 * @return string
	 */
	public function getFormat() {
		return $this->image->getImageFormat();
	}

	/**
	 * Corrects image orientation based on EXIF metadata
	 *
	 * @return $this
	 */
	public function autorotate() {

		switch ( $this->image->getImageOrientation() ) {

			case \Imagick::ORIENTATION_TOPLEFT:
				break;

			case \Imagick::ORIENTATION_TOPRIGHT:
				$this->image->flopImage();
				break;

			case \Imagick::ORIENTATION_BOTTOMRIGHT:
				$this->image->rotateImage( "#000", 180 );
				break;

			case \Imagick::ORIENTATION_BOTTOMLEFT:
				$this->image->flopImage();
				$this->image->rotateImage( "#000", 180 );
				break;

			case \Imagick::ORIENTATION_LEFTTOP:
				$this->image->flopImage();
				$this->image->rotateImage( "#000", - 90 );
				break;

			case \Imagick::ORIENTATION_RIGHTTOP:
				$this->image->rotateImage( "#000", 90 );
				break;

			case \Imagick::ORIENTATION_RIGHTBOTTOM:
				$this->image->flopImage();
				$this->image->rotateImage( "#000", 90 );
				break;

			case \Imagick::ORIENTATION_LEFTBOTTOM:
				$this->image->rotateImage( "#000", - 90 );
				break;

		}

		$this->image->setImageOrientation( \Imagick::ORIENTATION_TOPLEFT );

		return $this;
	}

	/**
	 * Compress image
	 *
	 * @param int $quality Quality level
	 * @param int $type Compression type
	 *
	 * @return $this
	 */
	public function compress( $quality = 90, $type = \Imagick::COMPRESSION_JPEG ) {
		$this->image->setImageCompression( $type );
		$this->image->setImageCompressionQuality( $quality );

		return $this;
	}

	/**
	 * Automatically optimize image
	 *
	 * @param int $quality
	 *
	 * @return $this
	 */
	public function optimize( $quality = 90 ) {

		// Don't touch animated images
		if ( ! $this->isAnimated() ) {

			switch ( $this->getFormat() ) {

				case 'GIF':
					$this->autorotate()->stripMetadata();
					break;

				case 'JPEG':
					$this->autorotate()->stripMetadata()->compress( $quality );
					break;

				case 'PNG':
					$this->autorotate()->stripMetadata();
					break;

			}

		}

		return $this;

	}

	/**
	 * Strip metadata from image
	 *
	 * @return $this
	 */
	public function stripMetadata() {
		$this->image->stripImage();

		return $this;
	}

	/**
	 * Write file to filesystem
	 *
	 * @param string|null $filename
	 *
	 * @return $this
	 */
	public function write( $filename = null ) {
		if ( $this->isAnimated() ) {
			$this->image->writeImages( $filename, true );
		} else {
			$this->image->writeImage( $filename );
		}

		return $this;
	}

}
