<?php
/**
 *   +----------------------------------------------------------------------
 *   | PROJECT:   [ KaadonThinkBase ]
 *   +----------------------------------------------------------------------
 *   | 官方网站:   [ https://developer.kaadon.com ]
 *   +----------------------------------------------------------------------
 *   | Author:    [ kaadon <kaadon.com@gmail.com> codemiracle]
 *   +----------------------------------------------------------------------
 *   | Tool:      [ PhpStorm ]
 *   +----------------------------------------------------------------------
 *   | Date:      [ 2024/6/24 ]
 *   +----------------------------------------------------------------------
 *   | 版权所有    [ 2020~2024 kaadon.com ]
 *   +----------------------------------------------------------------------
 **/

namespace Kaadon\ThinkBase\traits;

use think\Model;

/**
 *
 */
trait ModelTrait
{
    /**
     * 清除缓存
     * @param Model $model
     * @return void
     */
    public static function clearCache(Model $model): void
    {
    }


    /**
     * @param \think\Model $model
     * @return void
     */
    public static function onAfterWrite(Model $model): void
    {
        self::clearCache($model);
    }

    /**
     * @param \think\Model $model
     * @return void
     */
    public static function onAfterDelete(Model $model): void
    {
        //逻辑代码
        self::clearCache($model);
    }


}