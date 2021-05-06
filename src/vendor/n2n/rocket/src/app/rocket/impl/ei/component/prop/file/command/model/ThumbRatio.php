<?php
namespace rocket\impl\ei\component\prop\file\command\model;

use n2n\io\managed\img\ImageDimension;
use n2n\util\type\ArgUtils;

class ThumbRatio {
	const STR_ATTR_SEPERATOR = '-';
	private $width;
	private $height;
	private $crop;
	
	public function __construct(int $width, int $height, bool $crop = false) {
		$this->width = $width;
		$this->height = $height;
		$this->crop = $crop;
	}
	
	public function buildLabel() {
		return $this->width . ' / ' . $this->height;
	}
	
	public function __toString() {
		return $this->width . self::STR_ATTR_SEPERATOR . $this->height . ($this->crop ? self::STR_ATTR_SEPERATOR . 'crop' : '');
	}
	
	/**
	 * @param mixed $expr
	 * @return ThumbRatio
	 */
	public static function create($expr) {
		if ($expr instanceof ImageDimension) {
			return self::fromImageDimension($expr);
		}
		
		$expr = ArgUtils::toString($expr);
		
		$parts = explode(ImageDimension::STR_ATTR_SEPARATOR, (string) $expr);
		ArgUtils::assertTrue(count($parts) === 2 || count($parts) === 3);
		
		return new ThumbRatio($parts[0], $parts[1], (count($parts) === 3 ? true : false));
	}
	
	private static function fromImageDimension(ImageDimension $imageDimension) {
		$width = $imageDimension->getWidth();
		$height = $imageDimension->getHeight();
		$ggt = self::gcd($width, $height);
		
		return new ThumbRatio($width / $ggt, $height / $ggt, null !== $imageDimension->getIdExt());
	}
	
	private static function gcd($num1, $num2) {
		if ($num2 === 0) return $num1;
		
		return self::gcd($num2, $num1 % $num2);
	}
}