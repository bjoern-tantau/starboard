<?php

/**
 * Add Blade extensions to be used in the templates.
 * @see http://laravel.com/docs/templates#extending-blade
 */

/**
 * @errors('field')
 */
Blade::extend(function($view, $compiler){
    /* @var $compiler \Illuminate\View\Compilers\BladeCompiler */
    $pattern = $compiler->createMatcher('errors');
    $replacement = '$1<?php if ($errors->has($2)): ?>'
        . '<ul class="errors $2">'
        . '<?php foreach ($errors->get($2) as $error): ?>'
        . '<li class="error"><?php echo $error ?></li>'
        . '<?php endforeach; ?>'
        . '</ul>'
        . '<?php endif; ?>';
    return preg_replace($pattern, $replacement, $view);
});

/**
 * @errors
 */
Blade::extend(function($view, $compiler){
    /* @var $compiler \Illuminate\View\Compilers\BladeCompiler */
    $pattern = $compiler->createPlainMatcher('errors');
    $replacement = '$1<?php if ($errors->has()): ?>'
        . '<ul class="errors">'
        . '<?php foreach ($errors->all() as $error): ?>'
        . '<li class="error"><?php echo $error ?></li>'
        . '<?php endforeach; ?>'
        . '</ul>'
        . '<?php endif; ?>';
    return preg_replace($pattern, $replacement, $view);
});

