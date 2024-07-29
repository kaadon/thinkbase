<?php
declare(strict_types=1);

namespace Kaadon\ThinkBase\abstracts;

use Kaadon\ThinkBase\interfaces\JobsInterface;
use ReflectionMethod;
use think\facade\Log;
use think\facade\Queue;
use think\queue\Job;

/**
 * 队列基类
 */
abstract class BaseJobs implements JobsInterface
{
    /**
     * @var string
     */
    public string $down = "\n 👉👉👉";
    /**
     * @var string
     */
    public string $error = '';
    /**
     * @var array
     */
    public array $JobData;
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
        $this->JobData   = $data;
        echo "{$this->down}任务名称: ["  .  ($this->JobData['task'] ??  '任务名称---错误').  "] \n";
        $this->jobChanel = json_decode($job->getRawBody(), true)['job'];
        echo $this->down . '任务数据:' . "\n";
        print_r($this->JobData);
        echo "\n \n";

        if ($job->attempts() > 3) {
            $job->delete();
            echo  "{$this->down} 执行[{$job->getJobId()}]超过 {$job->attempts()} 次错误: {$this->error} ❌ ,删除任务! \n";
        }else{
            try {
                //逻辑代码
                $execute = $this->doJOb();
                if ($execute) {
                    $job->delete();
                    echo "{$this->down} 执行[{$job->getJobId()}]第 {$job->attempts()} 次任务: 成功 ✅ !,删除任务! \n";
                } else {
                    if ($job->attempts() > 2) {
                        $job->delete();
                        echo "{$this->down} 执行[{$job->getJobId()}]第 {$job->attempts()} 次失败 ❌ ,错误为:: {$this->error},删除任务! \n";
                    }else{
                        echo "{$this->down} 执行[{$job->getJobId()}]第 {$job->attempts()} 次失败 ❌ ,错误为:: {$this->error} \n";
                    }
                }
            } catch (\Exception $exception) {
                echo "{$this->down} 错误: {$exception->getMessage()} \n";
                $job->delete();
            }
        }

    }

    /**
     * 队列执行
     * @return bool
     */
    public function doJOb(): bool
    {
        if (
            array_key_exists('task', $this->JobData) //判断任务是否存在
            && method_exists($this->jobChanel, $this->JobData['task']) //方法是否存在
            && array_key_exists('data', $this->JobData) // 数据是否存在
            && is_array($this->JobData['data'])//数据必须是数组
        ) {
            echo "♻️♻️♻️ 业务执行中... \n";
            try {
                $task = $this->JobData['task'];
                $reflection = new ReflectionMethod($this, $task);
                if ($reflection->isStatic()) {
                    $bool = $this::$task($this->JobData['data']);
                }else{
                    $bool = $this->$task($this->JobData['data']);
                }
            } catch (\Exception $exception) {
                $this->error = $exception->getMessage();
                $bool = false;
            }
            echo "\n♻️♻️♻️ 业务执行结束\n";
            return $bool;
        } else {
            $this->error = "⁉️请检查参数!";
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