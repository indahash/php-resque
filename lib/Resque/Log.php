<?php
/**
 * Resque default logger PSR-3 compliant
 *
 * @package		Resque/Stat
 * @author		Chris Boulton <chris@bigcommerce.com>
 * @license		http://www.opensource.org/licenses/mit-license.php
 */
class Resque_Log extends Psr\Log\AbstractLogger
{
	public $verbose;

	public function __construct($verbose = false) {
		$this->verbose = $verbose;
	}

	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed   $level    PSR-3 log level constant, or equivalent string
	 * @param string  $message  Message to log, may contain a { placeholder }
	 * @param array   $context  Variables to replace { placeholder }
	 * @return null
	 */
	public function log($level, $message, array $context = array())
	{
	    $this->logPhalconError($level, $message, $context);

		if ($this->verbose) {
			fwrite(
				STDOUT,
				'[' . $level . '] [' . (new DateTime())->format('H:i:s Y-m-d')) . '] ' . $this->interpolate($message, $context) . PHP_EOL
			);
			return;
		}

		if (!($level === Psr\Log\LogLevel::INFO || $level === Psr\Log\LogLevel::DEBUG)) {
			fwrite(
				STDOUT,
				'[' . $level . '] ' . $this->interpolate($message, $context) . PHP_EOL
			);
		}
	}

	/**
	 * Fill placeholders with the provided context
	 * @author Jordi Boggiano j.boggiano@seld.be
	 *
	 * @param  string  $message  Message to be logged
	 * @param  array   $context  Array of variables to use in message
	 * @return string
	 */
	public function interpolate($message, array $context = array())
	{
		// build a replacement array with braces around the context keys
		$replace = array();
		foreach ($context as $key => $val) {
			$replace['{' . $key . '}'] = $val;
		}

		// interpolate replacement values into the message and return
		return strtr($message, $replace);
	}

    /**
     * @param $level
     * @param $message
     * @param array $context
     */
    protected function logPhalconError($level, $message, array $context)
    {
        if (!in_array($level, [Psr\Log\LogLevel::INFO, Psr\Log\LogLevel::NOTICE, Psr\Log\LogLevel::DEBUG])) {
            $di = \Phalcon\Di\Di::getDefault();
            $log = $di->get('log');

            if (!method_exists($log, $level)) {
                $level = 'error';
            }

            $log->$level($this->interpolate($message, $context));
        }
    }
}
