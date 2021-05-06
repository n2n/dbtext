<?php
namespace n2n\log4php\appender\nn6;
use DateTime;
use n2n\io\fs\FsPath;

use n2n\core\VarStore;

use n2n\core\N2N;

use n2n\log4php\LoggerAppender;
use n2n\log4php\LoggerException;
use n2n\log4php\logging\LoggingEvent;
use n2n\mail\Mail;
use n2n\io\IoUtils;

/**
 * Adminmailcenter uses the SimpleXML function to output events. 
 * 
 * This Appender Uses A Layout
 * 
 * ## Configurable parameters: ##
 * 
 * - **htmlLineBreaks** - If set to true, a <br /> element will be inserted 
 *     before each line break in the logged message. Default is false.
 *
 * @version $Revision: 1337820 $
 * @package log4php
 * @subpackage appenders
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link http://logging.apache.org/log4php/docs/appenders/echo.html Appender documentation
 */
class AdminMailCenter extends LoggerAppender {
	protected $file;

	const LOG_FOLDER = 'mail';
	const DEFAULT_MAIL_FILE_NAME = 'mails.xml';

	const TAG_NAME_MAIL_ITEMS = 'logItems';
	const TAG_NAME_ITEM = 'item';
	const TAG_NAME_MESSAGE = 'message';
	const TAG_NAME_SUBJECT = 'subject';
	const TAG_NAME_TO = 'to';
	const TAG_NAME_FROM = 'from';
	const TAG_NAME_CC = 'cc';
	const TAG_NAME_BCC = 'bcc';
	const TAG_NAME_REPLY_TO = 'replyTo';
	const TAG_NAME_TEXT_ONLY = 'textOnly';
	const TAG_NAME_ATTACHMENTS = 'attachments';
	const TAG_NAME_ATTACHMENT = 'attachment';
	const TAG_NAME_PATH = 'path';
	const TAG_NAME_NAME = 'name';

	private $severity;

	public function __construct($file = null) {
		parent::__construct();
		$this->setFile($file);
	}

	public function setFile($file) {
		$datedFileName = self::replaceDatePlaceholders($file);
		$this->setString('file', $datedFileName);
	}

	public function getFile() {
		return $this->file;
	}
	
	public function activateOptions() {
		if (empty($this->file)) {
			$this->warn("Required parameter 'file' not set. Closing appender.");
			$this->closed = true;
			return;
		}
		$this->closed = false;
	}
	
	public function append(LoggingEvent $event) {
		$this->severity = $event->getLevel()->toString();
		$message = $event->getMessage();
		if ($message instanceof Mail) {
			$this->logMail($message);
		} else {
			throw new LoggerException("The message for the AdminMailCenter has to be an instance of n2n\\mail\\Mail");
		}
	}

	/**
	 * Scans mail log directory and return array containing the files available
	 * @return array|FsPath[]
	 */
	public static function scanMailLogFiles() {
		$mailLogsDir = new FsPath(self::getTargetDirectoryPath());
		return $mailLogsDir->getChildren();
	}

	/**
	 * Checks if the log file for year and month exists.
	 * @param int $year
	 * @param int $month
	 * @return bool
	 */
	public static function logFileExists(string $filename) {
		foreach (self::scanMailLogFiles() as $mailLogFile) {
			if ($mailLogFile->getName() === $filename) {
				return true;
			}
		}

		return false;
	}

	private function logMail(Mail $mail) {
		$this->checkAndCreateFile();
		$now = new DateTime();
		$xmlStr = "\t" . '<' . self::TAG_NAME_ITEM . ' severity="' . htmlspecialchars($this->severity) . '" datetime="' . htmlspecialchars($now->format(DateTime::ATOM)) . '">' . "\r\n";
		$items = array(
				self::TAG_NAME_MESSAGE => $mail->getMessage(),
				self::TAG_NAME_SUBJECT => $mail->getSubject(),
				self::TAG_NAME_TO => $mail->getTo(),
				self::TAG_NAME_FROM => $mail->getFrom(),
				self::TAG_NAME_CC => $mail->getCc(),
				self::TAG_NAME_BCC => $mail->getBcc(),
				self::TAG_NAME_REPLY_TO => $mail->getReplyTos(),
				self::TAG_NAME_TEXT_ONLY => !$mail->isHtml()
		);
		
		$attachments = array();
		$attachments[self::TAG_NAME_ATTACHMENT] = array();
		foreach ($mail->getAttachments() as $path => $name) {
			$attachments[self::TAG_NAME_ATTACHMENT][] = array(self::TAG_NAME_PATH => $path, self::TAG_NAME_NAME => $name);
		}
		$items[self::TAG_NAME_ATTACHMENTS] = $attachments;
		
		$xmlStr .= $this->buildXml($items, 2);
		$xmlStr .= "\t" . '</item>' . "\r\n" .
				'</' . self::TAG_NAME_MAIL_ITEMS .'>';
		$fileRes = IoUtils::fopen($this->getTargetFilePath(), "r+");
		IoUtils::flock($fileRes, LOCK_EX);
		IoUtils::fseek($fileRes, -11, SEEK_END);
		IoUtils::fwrite($fileRes, $xmlStr);
		IoUtils::flock($fileRes, LOCK_UN);
		
		fclose($fileRes);
	}
	
	private function buildXml(array $items, $level) {
		$prefix = str_repeat("\t", $level);
		$xmlContents = '';
		foreach ($items as $name => $value) {
			if (is_array($value)) {
				if ($this->areArrayKeysGenerated($value)) {
					//for tags with the same tag names
					foreach ($value as $value) {
						if (is_array($value)) {
							$xmlContents .= $prefix . '<' . htmlspecialchars($name) . '>' . "\r\n";
							$xmlContents .= $this->buildXml($value, $level + 1);
							$xmlContents .= $prefix . '</' . htmlspecialchars($name) . '>' . "\r\n";
						} else {
							$xmlContents .= $prefix . '<' . htmlspecialchars($name) . '>' .
									htmlspecialchars($value) . '</' . htmlspecialchars($name) . '>' . "\r\n";
						}
					} 	
				} else {
					$xmlContents .= $prefix . '<' . htmlspecialchars($name) . '>' . "\r\n";
					$xmlContents .= $this->buildXml($value, $level + 1);
					$xmlContents .= $prefix . '</' . htmlspecialchars($name) . '>' . "\r\n";
				}
			} else {
				$xmlContents .= $prefix . '<' . htmlspecialchars($name) . '>' .
				htmlspecialchars($value) . '</' . htmlspecialchars($name) . '>' . "\r\n";
			}
		}
		return $xmlContents;
	}
	
	private function checkAndCreateFile() {
		if (!$this->getTargetFilePath()->isFile()) {
			IoUtils::putContentsSafe($this->getTargetFilePath(),
						'<?xml version="1.0" encoding="UTF-8"?>' . "\r\n"
			. '<' . self::TAG_NAME_MAIL_ITEMS . '>' . "\r\n"
			. '<' . self::TAG_NAME_MAIL_ITEMS . '>');
		}
	}

	private function getTargetFilePath() {
		return new FsPath(self::getTargetDirectoryPath() . DIRECTORY_SEPARATOR . $this->file);
	}

	private static function getTargetDirectoryPath() {
		return N2N::getVarStore()->requestDirFsPath(VarStore::CATEGORY_LOG, N2N::NS,
				self::LOG_FOLDER, true);
	}

	private function areArrayKeysGenerated(array $arr) {
		foreach (array_keys($arr) as $key => $value) {
			if (!($key === $value)) return false;
		}
		return true;
	}

	/**x
	 * Replaces values in-between curly brackets {} in $str by {@see date()} function values.
	 * @param string $str
	 * @return string
	 */
	public static function replaceDatePlaceholders(string $str): string {
		return preg_replace_callback('/{(.*?)}/',
			function ($matches) { return date($matches[1]); },
			$str);
	}
}

