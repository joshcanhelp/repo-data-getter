<?php
declare(strict_types=1);

namespace DxSdk\Data;

class Logger {

	private $now;
	private $theLog = [];

	/**
	 * Logger constructor.
	 *
	 * @param string $date - Date/time for process start to use in log filename.
	 */
	public function __construct( $date ) {
		$this->now = $date;
	}

	/**
	 * @param string $msg
	 *
	 * @return Logger
	 */
	public function log( string $msg ): Logger {
		$this->theLog[] = sprintf( '[%s] %s', date( 'c' ), $msg );
		return $this;
	}

	/**
	 * @return string
	 */
	public function out(): string {
		return PHP_EOL . implode( PHP_EOL, $this->theLog );
	}

	/**
	 * @return bool
	 */
	public function save(): bool {
		if ( isset( $_SERVER['REQUEST_TIME_FLOAT'] ) ) {
			$this->log( 'Done in ' . round( microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 1 ) . 's' );
		}
		$fileName = $this->now  . '.txt';
		return (bool) file_put_contents( 'logs/' . $fileName, $this->out() );
	}
}
