<?php
/**
 * Created by Bart Decorte
 * Date: 14/06/2020
 * Time: 18:29
 */
namespace App;

use Illuminate\View\Compilers\BladeCompiler as BaseBladeCompiler;

class BladeCompiler extends BaseBladeCompiler
{
    /**
     * Compile the component tags.
     *
     * @param  string  $value
     * @return string
     */
    protected function compileComponentTags($value)
    {
        if (! $this->compilesComponentTags) {
            return $value;
        }

        return (new ComponentTagCompiler(
            $this->classComponentAliases
        ))->compile($value);
    }
}
