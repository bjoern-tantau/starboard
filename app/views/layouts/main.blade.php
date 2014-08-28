<?xml version="1.0" encoding="UTF-8"?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" version="XHTML+RDFa 1.0" xml:lang="{{{ $lang or 'en' }}}">
    <head>
        <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=UTF-8" />
        <title>{{{ $title or 'Starboard' }}}</title>
        {{ HTML::script('js/jquery.js') }}
        {{ HTML::script('js/autobahn.js') }}
        {{ HTML::script('js/kinetic.js') }}
        @yield('head')
    </head>
    <body>
        <ul class="nav">
            <li>{{ link_to('/', trans('Home')) }}</li>
            @if(Auth::check())
            <li>{{ link_to_action('GameController@getIndex', trans('Games')) }}</li>
            <li>{{ link_to_route('user.show', trans('User Settings'), Auth::user()->id) }}</li>
            @if (Auth::user()->isAdmin)
            <li>{{ link_to_route('user.index', trans('Users')) }}</li>
            @endif
            <li>{{ link_to_action('AuthController@getLogout', trans('Logout')) }}</li>
            @else
            <li>{{ link_to_action('AuthController@getLogin', trans('Login')) }}</li>
            <li>{{ link_to_route('user.create', trans('Register')) }}</li>
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