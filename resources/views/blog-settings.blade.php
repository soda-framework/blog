@extends(soda_cms_view_path('layouts.inner'))

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{ route('soda.home') }}">Home</a></li>
        <li><a href="{{ route('soda.cms.blog.index') }}">{{ ucfirst(trans('soda-blog.blog-singular')) }}</a></li>
        <li class="active">Settings</li>
    </ol>
@stop

@section('head.title')
    <title>{{ ucfirst(trans('soda-blog.blog-singular')) }} Settings</title>
@endsection

@include(soda_cms_view_path('partials.heading'), [
    'icon'        => 'fa fa-book',
    'title'       => ucfirst(trans('soda-blog.blog-singular')) . ' Settings',
])

@section('content')
    <div class="content-block">
        <ul>
            <li class="form-group">
                {!! Form::label('name', 'Name:') !!}
                {!! Form::input('name', 'name', null,['class' => 'blog-name form-control']) !!}
            </li>

            <li class="form-group">
                {!! Form::label('slug', 'Root Slug:') !!}
                {!! Form::input('slug', 'slug', null, ['class' => 'blog-slug form-control']) !!}
            </li>

            <li class="form-group">
                {!! Form::label('cms_slug', 'CMS Slug:') !!}
                {!! Form::input('cms_slug', 'cms_slug', null, ['class' => 'cms-slug form-control']) !!}
            </li>

            <li class="form-group">
                {!! Form::label('single_view', 'Blog View:') !!}
                {!! Form::input('single_view', 'single_view', null, ['class' => 'single-view form-control']) !!}
            </li>
        </ul>
    </div>
@endsection
