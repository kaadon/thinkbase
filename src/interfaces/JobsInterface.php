<?php
declare(strict_types=1);

namespace Kaadon\ThinkBase\interfaces;

use think\queue\Job;

/**
 *
 */
interface JobsInterface
{
    /**
     * @param Job $job
     * @param array $data
     * @return void
     */
    public function fire(Job $job, array $data): void;

    /**
     * @return bool
     */
    public function doJOb(): bool;

}