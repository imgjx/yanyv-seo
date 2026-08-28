<?php
/**
 * ===========================================================================
 * YanyvSEO - AI文章任务模型（生成/重写的进度状态载体）
 * ===========================================================================
 */
namespace app\model\cms;

use app\model\Base;

class AiTask extends Base
{
    protected $name = 'cms_aitask';

    protected $pk = 'taskid';

    /**
     * 追加一条进度日志（保留最近200行）
     */
    public function pushLog(string $line): void
    {
        $logs = $this->logs ? explode("\n", strval($this->logs)) : [];
        $logs[] = '['.date('H:i:s').'] '.$line;
        if(count($logs) > 200) $logs = array_slice($logs, -200);
        $this->save(['logs'=>implode("\n", $logs), 'upd_time'=>time()]);
    }

    /**
     * 任务状态输出
     */
    public function status(): array
    {
        return [
            'taskid' => intval($this->taskid),
            'type'   => strval($this->type),
            'total'  => intval($this->total),
            'done'   => intval($this->done),
            'fail'   => intval($this->fail),
            'offset' => intval($this->offset),
            'state'  => intval($this->state),
            'logs'   => strval($this->logs),
        ];
    }
}
