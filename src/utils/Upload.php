<?php

namespace Kaadon\ThinkBase\utils;


use Exception;
use Kaadon\Helper\GdImageHelper;
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
     * @param \think\File $file
     * @param array $upload_config
     * @return array
     * @throws \Exception
     */
    public function upload(\think\File $file, array $upload_config = []): array
    {
        $this->config = array_merge($this->config, $upload_config);
        $upload_type = $this->config['uploadType'] ?? 'local';
        $catePath = $this->config['catePath'] ?? 'system';
        $res = match ($upload_type) {
            "local" => $this->localUpload($file, $catePath,$file->extension()),
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
                    'original_mime_type' => $file->getOriginalMime(),
                    'mime_type' => $res['mime']??'',
                    'file_ext' => $res['extension']??'',
                    'url' => "{$res['domain']}{$res['path']}",
                    'sha1' => $file->hash(),
                    'md5' => $res['md5']??'',
                    'file_size' => $file->getSize(),
                ]);
            }
        }
        $res['url'] = "{$res['domain']}{$res['path']}";
        return $res;
    }

    /**
     * @param \think\File $file
     * @param string $filename
     * @param string $extension
     * @return array
     * @throws \Exception
     */
    public function localUpload(\think\File $file, string $filename = "system",string $extension = 'webp' ): array
    {
        try {
            //逻辑代码
            if (GdImageHelper::isSupportSuffix($extension)) {
                (new GdImageHelper($file->getRealPath(),$file->extension()))->convertTo($file->getRealPath(), $extension);
                $file= new \think\File($file->getRealPath());
                $file->setExtension('webp');
            }else{
                $extension = $file->extension();
            }
            $saveName = Filesystem::disk('public')->putFile($filename, $file);
            return [
                'domain' => $this->config['domain']??'',
                'md5' => $file->md5(),
                'mime' => $file->getMime(),
                'extension' => $extension,
                'path' => "/storage/" . str_replace(DIRECTORY_SEPARATOR, '/', $saveName)
            ];
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage());
        }
    }
}
