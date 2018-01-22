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
namespace Fratily\Application\Controller;

use Psr\Container\ContainerInterface;

/**
 *
 */
abstract class Controller{

    /**
     * DIコンテナ
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor
     *
     * @param   ContainerInterface  $container
     */
    public function __construct(ContainerInterface $container){
        $this->container        = $container;
    }

    public function __get($id){
        return $this->container->get($id);
    }

    public function __isset($id){
        return $this->container->has($id);
    }
}