<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" version="XHTML+RDFa 1.0" xml:lang="{{{ $lang or 'en' }}}">
    <head>
        <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
        <title>{{{ $title or 'Starboard' }}}</title>
    </head>
    <body>
        <ul class="nav">
            <li>{{ link_to('/', trans('Home')) }}</li>
            @if(Auth::check())
            <li>{{ link_to_action('AuthController@getLogout', trans('Logout')) }}</li>
            @else
            <li>{{ link_to_action('AuthController@getLogin', trans('Login')) }}</li>
            @endif
        </ul>
        @if(isset($message))
        <div class="message">
            {{{ $message }}}
        </div>
        @endif
        @if($globalErrors)
            @errors
        @endif
        <div class="content">
            @yield('content')
            {{ $content }}
        </div>
    </body>
</html>