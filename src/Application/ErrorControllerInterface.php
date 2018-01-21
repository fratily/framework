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
namespace Fratily\Application;

/**
 *
 */
class ErrorControllerInterface{

    /**
     * \Fratily\Http\Status\HttpStatusをキャッチした場合に実行されるアクション
     *
     * @param   int $status
     *
     * @return  string
     */
    public function status(int $status): string;

    /**
     * 例外をキャッチしデバッグモードでない場合に実行されるアクション
     *
     * @param   \Throwable  $e
     *
     * @return  string
     */
    public function throwable(\Throwable $e): string;
}