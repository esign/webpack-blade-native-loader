<?php
/**
 * Created by Bart Decorte
 * Date: 14/06/2020
 * Time: 18:26
 */
namespace App;

use Illuminate\Container\Container;
use Illuminate\Contracts\View\Factory;
use Illuminate\View\Compilers\ComponentTagCompiler as BaseComponentTagCompiler;
use InvalidArgumentException;

class ComponentTagCompiler extends BaseComponentTagCompiler
{
    /**
     * @param \App\string $component
     * @return string
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function componentClass(string $component)
    {
        $viewFactory = Container::getInstance()->make(Factory::class);

        if ($viewFactory->exists($view = "components.{$component}")) {
            return $view;
        }

        if ($viewFactory->exists($view = "components.{$component}.index")) {
            return $view;
        }

        throw new InvalidArgumentException(
            "Unable to locate a class or view for component [{$component}]."
        );
    }
}
