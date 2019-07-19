<?php
declare(strict_types=1);

namespace DxSdk\Data;

use DxSdk\Data\Files\Files;

class Logger {

	use Files;

	private $theLog = [];

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
		echo $this->out();
		if ( isset( $_SERVER['REQUEST_TIME_FLOAT'] ) ) {
			$this->log( 'Done in ' . round( microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 1 ) . 's' );
		}
		return $this->writeToFile( 'logs/' . DATE_NOW . '.txt', $this->out() );
	}
}
