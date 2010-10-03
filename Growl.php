<?php

namespace Notification;


/**
 * Triggeres Growl system notification on Mac systems
 *
 *
 * @uses growl
 * @see http://growl.info/
 * 
 * @uses growlnotify
 * @see http://growl.info/extras.php
 *
 * 
 * @author Mikulas Dite
 * @copyright Mikulas Dite 2010
 *
 * 
 * @static
 */
class Growl
{

	const GROWL_COMMAND = '/usr/local/bin/growlnotify';
	const MINIMAL_VERSION = '0.7';



	/**
	 * @throws \LogicException
	 */
	public function __construct()
	{
		throw new \LogicException("Cannot instantiate static class " . get_class($this));
	}



	/**
	 * Looks for growlnotify, if found compares Growl version with required
	 *
	 * @throws \Notification\GrowlException
	 * @return bool
	 */
	private static function checkEnvironment()
	{
		$version = array();
		$res = array();

		exec(self::GROWL_COMMAND . ' --version', $res);
		if (preg_match('~growlnotify (?P<major>\d)\.(?P<minor>\d).(?P<build>\d)~', $res[0], $version)) {
			$minimal = explode('.', self::MINIMAL_VERSION);
			if (($version['major'] == $minimal[0] && $version['minor'] >= $minimal[1])
			 || ($version['major'] > $minimal[0])) {
				return TRUE;
			} else {
				$current = $version['major'] . '.' . $version['minor'];
				throw new GrowlException('Growl application is out of date, current version is `' . $current . '`, `' . self::MINIMAL_VERSION . '` or higher expected.');
			}
		}
		throw new GrowlException('Growl application not found in `' . self::GROWL_COMMAND . '`.');
	}



	/**
	 * Triggers notification
	 *
	 * @param string $message
	 * @param string $title
	 * @param string $appIcon
	 * @param int $priority
	 * @param bool $sticky
	 * @param bool $wait
	 *
	 * @throws \Notification\GrowlException
	 */
	public static function notify($message, $title = NULL, $appIcon = NULL, $priority = NULL, $sticky = NULL, $wait = NULL)
	{
		self::checkEnvironment();

		$command = self::GROWL_COMMAND;

		if ($message == '' || $message == '-') {
			throw new GrowlException('Notification message must not be empty string nor a `-`.');
		} else {
			$command .= " --message " . escapeshellarg($message);
		}
		
		if ($title != '')
		{
			$command .= " --title " . escapeshellarg($title);
		}
		
		if ($appIcon != '')
		{
			$command .= " --appIcon " . escapeshellarg($appIcon);
		}

		if ((int) $priority != '')
		{
			$command .= " --priority $priority";
		}

		if ((bool) $sticky) {
			$command .= " --sticky";
		}

		if ((bool) $wait)
		{
			$command .= " --wait";
		}

		exec(escapeshellcmd($command));
	}
}

class GrowlException extends \Exception
{
	
}
