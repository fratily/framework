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
namespace Fratily\Framework\Middleware;

use Fratily\Http\Message\Status\HttpStatus;
use Twig\Environment;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Interop\Http\Factory\ResponseFactoryInterface;

/**
 *
 */
class ErrorMiddleware implements MiddlewareInterface{

    /**
     * @var Environment
     */
    protected $twig;

    /**
     * @var ResponseFactoryInterface
     */
    protected $factory;

    /**
     * @var bool
     */
    protected $debug;

    /**
     * Constructor
     *
     * @param   Environment $twig
     * @param   ResponseFactoryInterface    $factory
     */
    public function __construct(
        Environment $twig,
        ResponseFactoryInterface $factory,
        bool $debug
    ){
        $this->twig     = $twig;
        $this->factory  = $factory;
        $this->debug    = $debug;
    }

    /**
     * {@inheritdoc}
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface{
        try{
            $response   = $handler->handle($request);
        }catch(\Throwable $e){
            $response   = $this->debug
                ? $this->createErrorPageInDebugMode($e)
                : $this->createErrorPage($e)
            ;
        }

        return $response;
    }

    /**
     * 例外を本番で出しても問題ないレスポンスに変換する
     *
     * @param   \Throwable  $e
     *
     * @return  ResponseInterface
     */
    protected function createErrorPage(\Throwable $e){
        $response   = $this->factory->createResponse(
            $e instanceof HttpStatus ? $e->getStatusCode : 500
        );

        if($e instanceof \Fratily\Http\Message\Status\MethodNotAllowed){
            $response   = $response->withHeader(
                "Allow",
                implode(", ", $e->getAllowed())
            );
        }

        return $response;
    }

    /**
     * デバッグモード用のエラーページ描画用のレスポンスインスタンスを生成する
     *
     * @param   \Throwable  $e
     *
     * @return  ResponseInterface
     */
    protected function createErrorPageInDebugMode(\Throwable $e){
        $response   = $this->createErrorPage($e);
        $context    = [
            "errors"    => [],
        ];

        do{
            $context["errors"][]    = $this->analysisError($e);
        }while(($e = $e->getPrevious()) !== null);

        $response->getBody()->write($this->twig->render("error.twig", $context));

        return $response;
    }

    protected function analysisError(\Throwable $e){
        $result = [
            "name"      => get_class($e),
            "message"   => $e->getMessage(),
            "code"      => $e->getCode(),
            "file"      => $e->getFile(),
            "line"      => $e->getLine(),
            "script"    => $this->getFileContents($e->getFile(), $e->getLine()),
            "trace"     => [],
        ];

        foreach($e->getTrace() as $t){
            $call   = ($t["class"] ?? "") . ($t["type"] ?? "")
                . ($t["function"] ?? "unknown") . "("
                . implode(", ", array_map([$this, "getDumpString"], $t["args"] ?? []))
                . ")"
            ;

            $result["trace"][]  = [
                "call"      => $call,
                "file"      => $t["file"] ?? "unknown",
                "line"      => $t["line"] ?? 0,
                "script"    => $this->getFileContents($t["file"] ?? null, $t["line"] ?? null),
            ];
        }

        return $result;
    }

    /**
     * ファイルの指定行と前後3行を取得する
     *
     * 行番号をキーとした連想配列を返す
     *
     * @param   string  $file
     * @param   int $line
     *
     * @return  string[]
     */
    protected function getFileContents($file, $line){
        if(!is_string($file) || !is_int($line)
            || !is_file($file) || !is_readable($file)
        ){
            return null;
        }

        $file       = new \SplFileObject($file, "r");
        $current    = 1;
        $contents   = [];
        $min        = $line - 3;
        $max        = $line + 3;

        foreach($file as $row){
            if($min <= $current && $current <= $max){
                $contents[$current] = $row;
            }

            $current++;
        }

        if(empty($contents)){
            return null;
        }

        return $contents;
    }

    /**
     * 変数の値をダンプ用文字列に変換する
     *
     * @param   mixed   $value
     *
     * @return  string
     */
    protected function getDumpString($value){
        switch(gettype($value)){
            case "boolean":
                return $value ? "TRUE" : "FALSE";
            case "integer":
            case "double":
                return (string)$value;
            case "string":
                return "'{$value}'";
            case "array":
                return "Array(" . count($value) . ")";
            case "object":
                return get_class($value);
            case "resource":
                return "resource(" . get_resource_type($value) . ")";
            case "resource (closed)":
                return "closed resource(" . get_resource_type($value) . ")";
            case "NULL":
                return "NULL";
        }

        return "unknown type";
    }
}