<?php
/**
 * FratilyPHP
 * 
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE
 * Redistributions of files must retain the above copyright notice.
 * 
 * @author      Kento Oka <oka.kento0311@gmail.com>
 * @copyright   (c) Kento Oka
 * @license     MIT
 * @since       1.0.0
 */
namespace Fratily\Utility;


/**
 * 
 */
class Security{
        
    public static function passwdGetInfo(string $hash): array{
        return password_get_info($hash);
    }
    
    public static function passwdHash(string $passwd, int $algo, array $options = []){
        return password_hash($passwd, $algo, $options);
    }
    
    public static function passwdVerify(string $passwd, string $hash): bool{
        return password_verify($passwd, $hash);
    }
    
    public static function passwdNeedsRehash(string $hash, int $algo, array $options = []): bool{
        return password_needs_rehash($hash, $algo, $options);
    }
}