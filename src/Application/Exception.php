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
namespace Fratily\Core;

/**
 * 
 */
class Exception extends \Exception{

    /**
     * The exception default message to throw.
     */
    const MSG   = "Thrown exception {call.in}.";

    /**
     * The exception default code to throw.
     */
    const CODE  = 0;

    /**
     * The exception data to throw.
     *
     * @var mixed[]
     */
    private $data   = [];

    private $allow  = true;

    /**
     * Constructor
     *
     * @param   string  $msg
     *      The exception message to throw.
     * @param   mixed[] $data
     *      The exception data to throw.
     * @param   \Throwable  $prev
     *      The prev exception used for the exception chaining.
     *
     * @return  void
     */
    public function __construct(
        string $msg = null,
        \Throwable $prev = null
    ){
        $this->code = static::CODE;

        $t  = $this->getTrace()[0] ?? [];

        $data   = array_merge(
            $this->data,
            [
                "exception.code"    => $this->code,
                "exception.file"    => $this->file,
                "exception.line"    => $this->line,
                "exception.in"      => "in {$this->file} {$this->line}",
                "call.func" => ($t["class"] ?? "")
                    . ($t["type"] ?? "")
                    . ($t["function"] ?? ":main")
                    . "()",
                "call.file" => $t["file"] ?? "#main",
                "call.line" => $t["line"] ?? 1,
                "call.in" => implode(" ", ["in", $t["file"] ?? "main", $t["line"] ?? 1])
            ]
        );

        parent::__construct(
            self::format($msg ?? static::MSG, $data), $this->code, $prev
        );

        $this->allow  = false;
    }

    /**
     * Gets the exception data.
     *
     * @param   string  $key
     *      Key of the Exception data.
     *
     * @return  mixed|null
     */
    final public function getData(string $key){
        return $this->data[$key] ?? null;
    }

    /**
     * Sets the exception data.
     *
     * @param   string  $key
     *      Key of the Exception data.
     * @param   mixed   $value
     *      Value of the Exception data.
     *
     * @return  void
     */
    final public function setData(string $key, $value): void{
        if($this->allow){
            $this->data[$key]   = $value;
        }
    }

    /**
     * コンテキストをメッセージに埋め込む
     *
     * @param   string  $msg
     *      The exception message to throw.
     * @param   mixed[] $context
     *      The exception data to throw.
     *
     * @return  string
     */
    final public static function format(string $msg, array $context = []): string{
        static $pattern = "/\{([a-zA-Z0-9_]+(?:\.[a-zA-Z0-9_]+)*)\}/u";

        if(!(bool)preg_match_all(
            $pattern, $msg, $matches, PREG_PATTERN_ORDER
        )){
            return $msg;
        }

        $matches    = array_unique($matches[1]);
        $search     = [];
        $replace    = [];

        foreach($matches as $match){
            $data       = $context[$match] ?? null;
            $search[]   = "{{$match}}";

//            foreach(explode(".", $match) as $key){
//                if(!isset($data[$key])){
//                    $data = null;
//                    break;
//                }
//
//                $data   = $data[$key];
//            }

            if(is_scalar($data) || (is_object($data) && method_exists($data, "__toString"))){
                $replace[]  = (string)$data;
            }else{
                $replace[]  = ":unknown:";
            }
        }

        return str_replace($search, $replace, $msg);
    }
}