@extends('soda-blog::default.layout')

@section('header')
    <title>Default {{ ucfirst(config('bootleg.blog.title_singular', 'blog')) }} Theme</title>
@stop

@section('content')
    This is the default {{ config('bootleg.blog.title_singular', 'blog') }} theme..:
    {{$post->name}}
    {{$post->getSetting('text')}}
@stop
