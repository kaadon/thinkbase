<?php
declare(strict_types=1);

namespace Kaadon\ThinkBase\abstracts;

use Kaadon\ThinkBase\interfaces\JobsInterface;
use ReflectionMethod;
use think\facade\Log;
use think\facade\Queue;
use think\queue\Job;

abstract class BaseJobs implements JobsInterface
{
    /**
     * @var string
     */
    public $error = '';
    /**
     * @var
     */
    public $JobData;
    /**
     * @var
     */
    public $jobChanel;

    /**
     * @param Job $job
     * @param array $data
     * @return void
     */
    public function fire(Job $job, array $data): void
    {
        $down = "👇👇👇";
        // TODO: Implement fire() method.
        $this->JobData   = $data;
        $this->jobChanel = json_decode($job->getRawBody(), true)['job'];
        if ($job->attempts() > 3) {
            $job->delete();
            Log::record($job->getRawBody(), 'queue');
            echo  "\n执行任务<" . $job->getJobId() .">". $down . " \n❌ 错误次数超过3次,删除任务" . "\n";
        }
        if ($this->doJOb()) {
            $job->delete();
            echo "\n执行<" . $job->getJobId() . ">任务". $down . "\n✅ 第" . $job->attempts() . "次成功,删除任务! \n";
        } else {
            if ($job->attempts() > 2) {
                $job->delete();
                echo "\n执行" . $job->attempts() . "次 <" . $job->getJobId() . ">失败". $down . " \n❌ 错误为::<". $this->error . ">,删除任务!" . "\n";
            }else{
                echo "\n执行<" . $job->getJobId() . ">失败". $down . "\n 已执行" . $job->attempts() . "次". $down . " \n❌ 错误为:". $this->error . ",\n";
            }
        }
    }

    /**
     * 队列执行
     * @return bool
     */
    public function doJOb(): bool
    {
        // TODO: Implement doJOb() method.
        if (
            array_key_exists('task', $this->JobData) //判断任务是否存在
            && method_exists($this->jobChanel, $this->JobData['task']) //方法是否存在
            && array_key_exists('data', $this->JobData) // 数据是否存在
            && is_array($this->JobData['data'])//数据必须是数组
        ) {
            try {
                $task = $this->JobData['task'];
                echo "\n 👉🏻 任务数据: \n" . preg_replace('/s/','',json_encode($this->JobData['data'],JSON_UNESCAPED_UNICODE)) . " \n";
                echo "\n 👉🏻 业务执行数据: \n";
                $reflection = new ReflectionMethod($this->jobChanel, $task);
                if ($reflection->isStatic()) {
                    $bool = $this->jobChanel::$task($this->JobData['data']);
                }else{
                    $bool = $this->jobChanel->$task($this->JobData['data']);
                }
                echo "\n 👉🏻 业务执行结果: \n";
            } catch (\Exception $exception) {
                $this->error = $exception->getMessage();
                return false;
            }
            return $bool;
        } else {
            $this->error = "请检查参数!";
            return false;
        }

    }

    /**
     * 队列推送
     * @param array $data
     * @param string $task
     * @param int $delay
     * @param string|null $queue
     * @param string|null $JobClass
     * @return bool|string
     */
    public static function Push(array $data, string $task, int $delay = 0, ?string $queue = null, ?string $JobClass = null): bool|string
    {
        if (empty($task)) {
            return false;
        }
        $queueData['task'] = $task;

        if (!empty($queue)) {
            $queueData['queue'] = $queue;
        } else {
            return false;
        }
        $queueData['data'] = $data;
        if ($delay > 0) {
            $bool = Queue::later($delay, $JobClass, $queueData, $queue);
        } else {
            $bool = Queue::push($JobClass, $queueData, $queue);
        }
        return $bool ?? false;
    }
}