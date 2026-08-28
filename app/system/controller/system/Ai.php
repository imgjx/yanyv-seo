<?php
/**
 * ===========================================================================
 * YanyvSEO - AI配置（OpenAI兼容API）
 * ===========================================================================
 */
namespace app\system\controller\system;

use app\system\controller\AdminBase;
use app\model\system\SystemSetting as S;

class Ai extends AdminBase
{
    /**
     * 配置页
     */
    public function index()
    {
        $rs = (new S())->listArray([['group','=','ai'],['state','=',1]],'name,title,value,type,options,private,tips');
        $data = [];
        foreach($rs as $v){
            if($v['private']) $v['value'] = half_replace(strval($v['value']));
            $v['placeholder'] = $v['tips'];
            $data[$v['name']] = $v;
        }
        $this->assign([
            'ready' => \tool\Ai::ready(),
            'items' => json_encode($data)
        ]);
        return $this->fetch();
    }

    /**
     * 保存配置
     */
    public function save()
    {
        $d = $this->only(['@token'=>''], 'post', 'strip_sql', false);
        $allow = ['ai_api_url','ai_api_key','ai_model','ai_temperature','ai_no_stream'];
        $time = time();
        foreach($allow as $name){
            if(!array_key_exists($name, $d)) continue;
            $row = S::one([['name','=',$name],['group','=','ai']]);
            if(!$row) continue;
            //私密字段未修改(带*)时跳过
            if($row->private && strpos(strval($d[$name]), '***') !== false) continue;
            S::where("name='$name'")->update(['value'=>strval($d[$name]), 'upd_time'=>$time, 'editor'=>$this->manUser['username']]);
        }
        S::cache(1);
        return $this->returnMsg('设置成功', 1);
    }
}
