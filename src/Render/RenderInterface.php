<?php
/**
 * FratilyPHP
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Kento Oka <kento-oka@kentoka.com>
 * @copyright   (c) Kento Oka
 * @license     MIT
 * @since       1.0.0
 */
namespace Fratily\Framework\Render;

interface RenderInterface{

    /**
     * 指定テンプレートに変数配列を渡し描画文字列を取得する
     *
     * @param   string  $path   テンプレートの指定に使う
     * @param   mixed[] $context    テンプレートに渡す変数配列
     *
     * @return  string
     */
    public function render(string $path, array $context = []): string;
}