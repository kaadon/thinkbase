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
 *   | Date:      [ 2024/2/27 ]
 *   +----------------------------------------------------------------------
 *   | 版权所有    [ 2020~2024 kaadon.com ]
 *   +----------------------------------------------------------------------
 **/

namespace Kaadon\ThinkBase\interfaces;

/**
 * @package Kaadon\ThinkBase\interfaces
 */
interface FileListenerInterface
{
    /**
     * @param array $params
     * @return void
     */
    public function handle(array $params): void;
}