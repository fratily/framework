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
namespace Fratily\Framework\Render;

use Fratily\Utility\Hash;
use Twig\Environment;

/**
 *
 */
class TwigRender implements RendererInterface{

    /*
     * @var Environment
     */
    private $twig;

    /**
     * Constructor
     *
     * @param   Environment $twig
     */
    public function __construct(Environment $twig){
        $this->twig = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public function render(string $path, array $context = []): string{
        $_context   = [];

        foreach($context as $key => $val){
            $_context   = Hash::set($_context, $key, $val);
        }

        return $this->twig->render($path, $context);
    }


}