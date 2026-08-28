<?php
/**
 * ===========================================================================
 * YanyvSEO - OpenAI兼容AI客户端
 * 配置项：ai_api_url / ai_api_key / ai_model / ai_temperature
 * ===========================================================================
 */
namespace tool;

class Ai
{
    /**
     * 是否已配置可用
     */
    public static function ready(): bool
    {
        return trim(strval(vconfig('ai_api_key'))) !== '' && trim(strval(vconfig('ai_api_url'))) !== '';
    }

    /**
     * 单轮对话补全
     * @param string $prompt  用户提示词
     * @param string $system  系统提示词(可选)
     * @return string  失败返回空串
     */
    public static function chat(string $prompt, string $system = ''): string
    {
        $rs = self::request([
            ['role' => 'system', 'content' => $system !== '' ? $system : '你是一名专业的中文内容编辑。'],
            ['role' => 'user',   'content' => $prompt],
        ]);
        return $rs;
    }

    /**
     * 并发批量对话（curl_multi 多线程）
     * @param array $prompts     键=>提示词
     * @param int   $concurrency 并发数(1-10)
     * @return array 键=>结果(失败为空串)
     */
    public static function chatMulti(array $prompts, int $concurrency = 3): array
    {
        $concurrency = max(1, min(10, $concurrency));
        $result = array_fill_keys(array_keys($prompts), '');
        $chunks = array_chunk($prompts, $concurrency, true);
        foreach($chunks as $chunk){
            $mh = curl_multi_init();
            $handles = [];
            foreach($chunk as $k => $p){
                $ch = self::handle([
                    ['role' => 'system', 'content' => '你是一名专业的中文内容编辑。'],
                    ['role' => 'user',   'content' => strval($p)],
                ]);
                curl_multi_add_handle($mh, $ch);
                $handles[$k] = $ch;
            }
            do{
                curl_multi_exec($mh, $running);
                if($running) curl_multi_select($mh, 0.5);
            }while($running);
            foreach($handles as $k => $ch){
                $result[$k] = self::parse(curl_multi_getcontent($ch));
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);
            }
            curl_multi_close($mh);
        }
        return $result;
    }

    /**
     * 执行请求并提取文本
     */
    protected static function request(array $messages): string
    {
        $ch = self::handle($messages);
        $body = curl_exec($ch);
        curl_close($ch);
        return self::parse($body);
    }

    /**
     * 构建 curl 句柄
     */
    protected static function handle(array $messages)
    {
        $payload = [
            'model'       => strval(vconfig('ai_model','gpt-4o-mini')),
            'messages'    => $messages,
            'temperature' => floatval(vconfig('ai_temperature',0.7)) ?: 0.7,
            'stream'      => false,
        ];
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => rtrim(strval(vconfig('ai_api_url')), '/').'/chat/completions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT        => 180,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer '.strval(vconfig('ai_api_key')),
            ],
        ]);
        return $ch;
    }

    /**
     * 解析响应文本
     */
    protected static function parse($body): string
    {
        if(!is_string($body) || $body === '') return '';
        $d = json_decode($body, true);
        $text = strval($d['choices'][0]['message']['content'] ?? '');
        if($text === '' && isset($d['error']['message'])) $text = '';
        return trim($text);
    }

}
