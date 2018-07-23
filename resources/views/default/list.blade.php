@extends('soda-blog::default.layout')

@section('header')
    <title>Default {{ ucfirst(config('bootleg.blog.title_singular', 'blog')) }} Theme</title>
@stop

@section('content')
    @foreach(\Soda\Blog\Models\Post::all() as $post)
        {{ $post }}
    @endforeach
@stop
