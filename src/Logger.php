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
	 * @return array
	 */
	public function log( string $msg ): array {
		$this->theLog[] = sprintf( '[%s] %s', date( 'c' ), $msg );
		return $this->theLog;
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
		$fileName = $this->now  . '.txt';
		$this->log( 'Saving to log ' . $fileName );
		return (bool) file_put_contents( 'logs/' . $fileName, $this->out() );
	}
}
