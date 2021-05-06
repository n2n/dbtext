<?php
namespace n2n\validation\lang;


use n2n\l10n\Message;
use n2n\io\managed\File;
use n2n\util\type\ArgUtils;

class ValidationMessages {
	const NS = 'n2n\validation';
	
	/**
	 * @param string $fieldName
	 * @return \n2n\l10n\impl\TextCodeMessage
	 */
	static function invalid(string $fieldName = null) {
		if ($fieldName === null) {
			return Message::createCode('invalid_err', null, self::NS);
		}
		
		return Message::createCodeArg('field_invalid_err', ['field' => $fieldName], null, self::NS);
	}
	
	/**
	 * @param string $fieldName
	 * @return \n2n\l10n\impl\TextCodeMessage
	 */
	static function required(string $fieldName = null) {
		if ($fieldName === null) {
			return Message::createCode('required_err', null, self::NS);
		}
		
		return Message::createCodeArg('field_required_err', ['field' => $fieldName], null, self::NS);
	}
	
	/**
	 * @param string $fieldName
	 * @return \n2n\l10n\impl\TextCodeMessage
	 */
	static function mandatory(string $fieldName = null) {
		if ($fieldName === null) {
			return Message::createCode('mandatory_err', null, self::NS);
		}
		
		return Message::createCodeArg('field_mandatory_err', ['field' => $fieldName], null, self::NS);
	}
	
	/**
	 * @param string $fieldName
	 * @return \n2n\l10n\impl\TextCodeMessage
	 */
	static function email(string $fieldName = null) {
		if ($fieldName === null) {
			return Message::createCode('email_err', null, self::NS);
		}
		
		return Message::createCodeArg('field_email_err', ['field' => $fieldName], null, self::NS);
	}
	
	/**
	 * @param string $maxlength
	 * @param string $fieldName
	 * @return \n2n\l10n\impl\TextCodeMessage
	 */
	static function maxlength(string $maxlength, string $fieldName = null) {
		if ($fieldName === null) {
			return Message::createCodeArg('maxlength_err', ['maxlength' => $maxlength], null, self::NS);
		}
		
		return Message::createCodeArg('field_maxlength_err', ['maxlength' => $maxlength, 'field' => $fieldName], 
				null, self::NS);
	}
	
	/**
	 * @param string $maxlength
	 * @param string $fieldName
	 * @return \n2n\l10n\impl\TextCodeMessage
	 */
	static function minlength(string $minlength, string $fieldName = null) {
		if ($fieldName === null) {
			return Message::createCodeArg('minlength_err', ['minlength' => $minlength], null, self::NS);
		}
		
		return Message::createCodeArg('field_minlength_err', ['minlength' => $minlength, 'field' => $fieldName],
				null, self::NS);
	}
	
	/**
	 * @param string $maxlength
	 * @param string $fieldName
	 * @return \n2n\l10n\impl\TextCodeMessage
	 */
	static function min(float $min, string $fieldName = null) {
		if ($fieldName === null) {
			return Message::createCodeArg('min_err', ['min' => $min], null, self::NS);
		}
		
		return Message::createCodeArg('field_min_err', ['min' => $min, 'field' => $fieldName],
				null, self::NS);
	}
	
	/**
	 * @param string $max
	 * @param string $fieldName
	 * @return \n2n\l10n\impl\TextCodeMessage
	 */
	static function max(float $max, string $fieldName = null) {
		if ($fieldName === null) {
			return Message::createCodeArg('max_err', ['max' => $max], null, self::NS);
		}
		
		return Message::createCodeArg('field_max_err', ['max' => $max, 'field' => $fieldName],
				null, self::NS);
	}
	
	/**
	 * @param int $min
	 * @param string $fieldName
	 * @return \n2n\l10n\impl\TextCodeMessage
	 */
	static function minElements(int $min, string $fieldName = null) {
		if ($fieldName === null) {
			return Message::createCode('min_elements_err', ['min' => $min], self::NS, $min);
		}
		
		return Message::createCodeArg('field_min_elements_err', ['field' => $fieldName, 'min' => $min], null, self::NS, $min);
	}
	
	/**
	 * @param int $max
	 * @param string $fieldName
	 * @return \n2n\l10n\impl\TextCodeMessage
	 */
	static function maxElements(int $max, string $fieldName = null) {
		if ($fieldName === null) {
			return Message::createCode('max_elements_err', ['max' => $max], self::NS, $max);
		}
		
		return Message::createCodeArg('field_max_elements_err', ['field' => $fieldName, 'max' => $max], null, self::NS, $max);
	}
	
	/**
	 * @param int $maxSize
	 */
	static function uploadMaxSize(int $maxSize, string $fileName, string $size, string $fieldName = null) {
		if ($fieldName === null) {
			return Message::createCodeArg('upload_size_err', ['fileName' => $fileName, 'size' => $size], 
					null, self::NS);
		}
		
		return Message::createCodeArg('field_upload_max_size_err', 
				['fileName' => $fileName, 'size' => $size, 'field' => $fieldName], null, self::NS);
	}
	
	/**
	 * @param string $fileName
	 * @param string $fieldName
	 * @return \n2n\l10n\impl\TextCodeMessage
	 */
	static function uploadIncomplete(string $fileName, string $fieldName = null) {
		if ($fieldName === null) {
			return Message::createCodeArg('upload_incomplete_err', ['fileName' => $fileName],
					null, self::NS);
		}
		
		return Message::createCodeArg('field_upload_incomplete_err',
				['fileName' => $fileName, 'field' => $fieldName], null, self::NS);
	}
	
	/**
	 * @param array $allowedValues
	 * @param string $fieldName
	 * @return \n2n\l10n\impl\TextCodeMessage
	 */
	static function enum(array $allowedValues, string $fieldName = null) {
		$allowedValuesStr = implode(', ', $allowedValues);
		
		if ($fieldName === null) {
			return Message::createCodeArg('enum_err', ['allowedValues' => $allowedValuesStr], null, self::NS);
		}
		
		return Message::createCodeArg('field_enum_err', ['allowedValues' => $allowedValuesStr, 'field' => $fieldName], 
				null, self::NS);
	}
	
	

// 	static function extension(string $fileName, array $allowedFileExtensions, string $fieldName = null) {
// 		if ($fieldName === null) {
// 			return Message::createCodeArg('invalid_file_extension_err',
// 					['fileName' => $fileName, 'allowedExtensions' => implode(', ', $allowedFileExtensions)],
// 					null, self::NS);
// 		}
		
// 		return Message::createCodeArg('field_invalid_file_extension_err',
// 				['fileName' => $fileName, 'allowedExtensions' => implode(', ', $allowedFileExtensions), 'field' => $fieldName],
// 				null, self::NS);
// 	}
	
// 	static function mimeType(string $givenMimeType, array $allowedMimeTypes, string $fieldName = null) {
// 		if ($fieldName === null) {
// 			return Message::createCodeArg('invalid_file_mime_type_err',
// 					['givenMimeType' => $givenMimeType, 'allowedMimeTypes' => implode(', ', $allowedMimeTypes)],
// 					null, self::NS);
// 		}
		
// 		return Message::createCodeArg('field_invalid_file_mimetype_err',
// 				['givenMimeType' => $givenMimeType, 'allowedMimeTypes' => implode(', ', $allowedMimeTypes), 'field' => $fieldName],
// 				null, self::NS);
// 	}
	
	/**
	 * @param File $file
	 * @param array $allowedTypeQualifiers
	 * @param string $fieldName
	 * @return \n2n\l10n\Message
	 */
	static function fileType(File $file, array $allowedTypeQualifiers, string $fieldName = null) {
		$fileStr = $file->getOriginalName() . ' (' . $file->getFileSource()->getMimeType() . ')';
		
		if ($fieldName === null) {
			return Message::createCodeArg('unsupported_file_type_err',
					['file' => $fileStr, 'allowedTypeQualifiers' => implode(', ', $allowedTypeQualifiers)],
					null, self::NS);
		}
		
		return Message::createCodeArg('field_unsupported_file_type_err',
				['file' => $fileStr, 'allowedTypeQualifiers' => implode(', ', $allowedTypeQualifiers), 
						'field' => $fieldName], null, self::NS);
	}
	
	static function imageResolution(string $imageName, string $fieldName = null) {
		if ($fieldName === null) {
			return Message::createCodeArg('image_resolution_err', ['image' => $imageName], null, self::NS);
		}
		
		return Message::createCodeArg('field_image_resolution_err', ['image' => $imageName, 'fieldName' => $fieldName], null, self::NS);
	}
	
	/**
	 * @param string $fieldName
	 * @return \n2n\l10n\impl\TextCodeMessage
	 */
	static function url(string $fieldName = null) {
		if ($fieldName === null) {
			return Message::createCode('url_err', null, self::NS);
		}
		
		return Message::createCodeArg('field_url_err', ['field' => $fieldName], null, self::NS);
	}
	
	/**
	 * @param string $fieldName
	 * @return \n2n\l10n\impl\TextCodeMessage
	 */
	static function urlSchemeRequired(string $fieldName = null) {
		if ($fieldName === null) {
			return Message::createCode('url_scheme_required_err', null, self::NS);
		}
		
		return Message::createCodeArg('field_url_scheme_required_err', ['field' => $fieldName], null, self::NS);
	}
	
	/**
	 * @param string[] $allowedSchemes
	 * @param string $fieldName
	 * @return \n2n\l10n\impl\TextCodeMessage
	 */
	static function urlScheme(array $allowedSchemes, string $fieldName = null) {
		ArgUtils::valArray($allowedSchemes, 'string');
		$allowedSchemesStr = implode(', ', $allowedSchemes);
		
		if ($fieldName === null) {
			return Message::createCodeArg('url_scheme_err', ['allowed_schemes' => $allowedSchemesStr], null, self::NS);
		}
		
		return Message::createCodeArg('field_url_scheme_err', 
				['allowed_schemes' => $allowedSchemesStr, 'field' => $fieldName],
				null, self::NS);
	}
	

	/**
	 * @param string $fieldName
	 * @return \n2n\l10n\impl\TextCodeMessage
	 */
	static function alreadyTaken(string $fieldName = null) {
		if ($fieldName === null) {
			return Message::createCode('already_taken_err', null, self::NS);
		}
		
		return Message::createCodeArg('field_already_taken_err', ['field' => $fieldName], null, self::NS);
	}
}