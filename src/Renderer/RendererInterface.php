<?php
/**
 * FratilyPHP
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Kento Oka <kento.oka@kentoka.com>
 * @copyright   (c) Kento Oka
 * @license     MIT
 * @since       1.0.0
 */
namespace Fratily\Renderer;

/**
 * 
 */
interface RendererInterface{
    
    /**
     * レンダリングを実行する
     * 
     * @param   string  $path
     *      ファイルパス
     * @param   mixed[] $vars
     *      変数リスト
     * 
     * @return  string
     */
    public function render(string $path, array $vars): string;
}