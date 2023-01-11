<?php

declare(strict_types=1);

namespace App\Exception;

final class StatusNotAllowedException extends \Exception
{
	public function __construct()
	{
		$message = 'Status value not allowed';

		parent::__construct($message);
	}
}
