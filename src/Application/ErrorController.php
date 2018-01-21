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

use Fratily\Reflection\ReflectionCallable;

/**
 *
 */
class ErrorController extends Controller{

    const TPL_THROWABLE = <<<TPL
<!DOCTYPE html>
<html>
    <head>
        <title>Thrown {name}</title>
    </head>
    <body>
        <h1>Thrown {name}</h1>
        <p>{message} in {file} {line}</p>
        <dl>
{trace}
        </dl>
    </body>
</html>
TPL;

    /**
     * {@inheritdoc}
     */
    public function status(int $code, array $params = []){
        if(method_exists($this, "http$code")){
            $action = new ReflectionCallable([$this, "http{$code}"]);

            return $action->invokeMapedArgs($params);
        }

        return "";
    }

    /**
     * {@inheritdoc}
     */
    public function throwable(\Throwable $e){
        $traces = [];

        foreach($e->getTrace() as $t){
            $class  = $t["class"] ?? "";
            $type   = $t["type"] ?? "";
            $func   = $t["function"] ?? "unknown";
            $args   = implode(", ", array_map(function($v){
                return \Fratily\Debug\Dumper::dumpSimple($v);
            }, $t["args"] ?? []));
            $file   = $t["file"] ?? "unkcnown";
            $line   = $t["line"] ?? 0;

            $traces[]   = "<dt>{$class}{$type}{$func}({$args})</dt><dd>{$file} {$line}</dd>";
        }

        $replace    = [
            "{name}"    => get_class($e),
            "{message}" => $e->getMessage(),
            "{file}"    => $e->getFile(),
            "{line}"    => $e->getLine(),
            "{trace}"   => implode(PHP_EOL . "            ", $traces)
        ];

        return str_replace(
            array_keys($replace),
            array_values($replace),
            self::TPL_THROWABLE
        );
    }
}