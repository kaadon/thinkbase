<?php

namespace Kaadon\ThinkBase\utils;


use Exception;
use think\facade\Event;
use think\facade\Filesystem;


/**
 *本地文件上传文件
 */
class Upload
{
    /**
     * @var array|mixed
     */
    public mixed $config = [];


    /**
     * @param array $upload_config
     */
    public function __construct(array $upload_config = [])
    {
        $this->config = !empty($upload_config) ? $upload_config : config('upload');
    }


    /**
     * @param $file
     * @param array $upload_config
     * @return array
     * @throws Exception
     */
    public function upload($file, array $upload_config = []): array
    {
        $this->config = array_merge($this->config, $upload_config);
        $upload_type = $this->config['uploadType'] ?? 'local';
        $catePath = $this->config['catePath'] ?? 'system';
        $res = match ($upload_type) {
            "local" => $this->localUpload($file, $catePath),
            default => throw new Exception("上传类型错误"),
        };
        $save_file = $this->config['saveFile'] ?? false;
        if ($res['path'] && $save_file) {
            $listener = $this->config['listener'] ?? null;
            $event = $this->config['event'] ?? null;
            if ($event && $listener) {
                Event::listen($event, $listener);
                Event::trigger($event, [
                    'upload_type' => $upload_type,
                    'original_name' => $file->getOriginalName(),
                    'mime_type' => $file->getOriginalMime(),
                    'file_ext' => strtolower($file->getOriginalExtension()),
                    'url' => "{$res['domain']}{$res['path']}",
                    'sha1' => $file->hash(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }
        $res['url'] = "{$res['domain']}{$res['path']}";
        return $res;
    }

    /**
     * @param $file
     * @param string $filename
     * @return array
     * @throws Exception
     */
    public function localUpload($file, string $filename = "system"): array
    {
        try {
            //逻辑代码
            $savename = Filesystem::disk('public')->putFile($filename, $file);
            return [
                'domain' => null,
                'path' => "/storage/" . str_replace(DIRECTORY_SEPARATOR, '/', $savename)
            ];
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }
}
