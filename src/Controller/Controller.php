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
namespace Fratily\Framework\Controller;

use Psr\Http\Message\ResponseInterface;
use Interop\Http\Factory\ResponseFactoryInterface;
use Twig\Environment;

/**
 *
 */
abstract class Controller{

    use \Fratily\Framework\Traits\TimelineTrait;
    use \Fratily\Framework\Traits\DumpTrait;
    use \Fratily\Framework\Traits\LogTrait;
    use \Fratily\Framework\Traits\EventTrait;

    /**
     * @var ResponseFactoryInterface
     */
    private $factory;

    /**
     * @var Environment
     */
    private $twig;

    private $debug;

    /**
     * Constructor
     *
     * @param   ContainerInterface  $container
     */
    public function __construct(
        ResponseFactoryInterface $factory,
        Environment $twig,
        bool $debug
    ){
        $this->factory  = $factory;
        $this->twig     = $twig;
        $this->debug    = $debug;
    }

    /**
     * アプリケーションがデバッグモードか確認する
     *
     * @return  void
     */
    protected function isDebug(){
        return $this->debug;
    }

    /**
     * レスポンスを生成する
     *
     * @param   int $code   HTTPレスポンスステータスコード
     *
     * @return  ResponseInterface
     *
     * @throws  ContainerNotFoundException
     */
    protected function response(int $code = 200){
        return $this->factory->createResponse($code);
    }

    /**
     * テンプレートエンジンのレンダリング結果を取得
     *
     * @param   string  $name
     * @param   mixed[] $context
     *
     * @return  string
     *
     * @throws  ContainerNotFoundException
     */
    protected function render(string $name, array $context = []){
        return $this->twig->render($name, $context);
    }
}