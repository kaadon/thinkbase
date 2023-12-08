<?php

namespace Kaadon\ThinkBase\utils;

use think\facade\Event;
use think\facade\Filesystem;

/**
 * 本地文件上传文件
 */
class Upload
{
    /**
     * @var array|mixed
     */
    public $config = [];

    /**
     *
     */
    public function __construct()
    {
        $this->config = config('upload');
    }


    /**
     * @param $file
     * @param array $upload_config
     * @return array
     * @throws \Exception
     */
    public function upload($file, array $upload_config = []): array
    {
        $name = $file->getOriginalName();
        $format = strrchr($name, '.');
        $filePath = $file->getRealPath();
        $fileName = date("Y") . date("m") . date("d") . uniqid() . $format;
        $upload_type = $upload_config['upload_type'] ?? 'local';
        $res = null;
        if ($upload_type == "local") {
            $res = $this->localUpload($file);
        }
        $save_file = $upload_config['save_file'];
        if ($res['path'] && $save_file) {
            Event::listen($upload_config['event'] ?? "uploadFile", $upload_config['listener']);
            Event::trigger($upload_config['event'] ?? "uploadFile", [
                'upload_type' => $upload_type,
                'original_name' => $file->getOriginalName(),
                'mime_type' => $file->getOriginalMime(),
                'file_ext' => strtolower($file->getOriginalExtension()),
                'url' => "{$res['domain']}{$res['path']}",
                'sha1' => $file->hash(),
                'file_size' => $file->getSize(),
            ]);
        }
        $res['url'] = "{$res['domain']}{$res['path']}";
        return $res;
    }

    /**
     * @param $file
     * @param string $filename
     * @return array
     * @throws \Exception
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
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }
}
