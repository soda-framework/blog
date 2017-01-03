<!doctype html>
<html lang="">
    <head>
        @section('header')
            <meta charset="utf-8">
            <title>Default {{ ucfirst(config('bootleg.blog.title_singular', 'blog')) }} Theme</title>
        @show
    </head>
    <body>
        @yield('content')
    </body>
</html>