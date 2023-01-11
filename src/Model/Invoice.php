<?php
declare(strict_types=1);

namespace Src\Model;

use App\Exception\StatusNotAllowedException;

/**
 * Class Invoice
 *
 * @property int $id
 * @property string $name
 * @property int $status,
 * @property int $pid
 *
 */
class Conversion
{
    const ID = 'id';
    const NAME = 'name';
    const STATUS = 'status';
    const PID = 'pid';

    const CONVERSION_STATUSES_IDS = [0, 1, 2];

    const CONVERSION_STATUSES = [
        'AWAIT' => 0,
        'PROCESSING' => 1,
        'PROCESSED' => 2
    ];

    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @throws StatusNotAllowedException
     */
    public function setStatus($status) {
        if (!self::isStatusAllowed($status)) {
            throw new StatusNotAllowedException();
        }
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function isStatusAllowed(int $status): bool
    {
        if (!in_array($status, self::CONVERSION_STATUSES_IDS)) {
            return false;
        }
        return true;
    }

}
