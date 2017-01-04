@extends(soda_cms_view_path('layouts.inner'))

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{ route('soda.home') }}">Home</a></li>
        <li><a href="{{ route('soda.cms.blog.index') }}">{{ ucfirst(trans('soda-blog::general.blog')) }}</a></li>
        <li class="active">Settings</li>
    </ol>
@stop

@section('head.title')
    <title>{{ ucfirst(trans('soda-blog::general.blog')) }} Settings</title>
@endsection

@section('content-heading-button')
    @include(soda_cms_view_path('partials.buttons.save'), ['submits' => '#blog-settings-form'])
@stop

@include(soda_cms_view_path('partials.heading'), [
    'icon'        => 'fa fa-book',
    'title'       => ucfirst(trans('soda-blog::general.blog')) . ' Settings',
])

@section('content')
    <div class="content-block">
        <form id="blog-settings-form" method="POST">
            {!! csrf_field() !!}
            {!! SodaForm::text([
                'name'        => 'Name',
                'field_name'  => 'name',
            ])->setLayout(soda_cms_view_path('partials.inputs.layouts.inline'))->setModel($blog) !!}

            {!! SodaForm::text([
                'name'        => 'Base slug',
                'field_name'  => 'slug',
                'description' => 'Base slug for ' . trans('soda-blog::general.blog') . ' ' . trans('soda-blog::general.posts')
            ])->setLayout(soda_cms_view_path('partials.inputs.layouts.inline'))->setModel($blog) !!}

            @permission('develop-blog')
            {!! SodaForm::text([
                'name'        => 'Single view',
                'field_name'  => 'single_view',
                'description' => 'View used for single ' . trans('soda-blog::general.blog') . ' ' . trans('soda-blog::general.posts')
            ])->setLayout(soda_cms_view_path('partials.inputs.layouts.inline'))->setModel($blog) !!}

            {!! SodaForm::text([
                'name'        => 'List view',
                'field_name'  => 'list_view',
                'description' => 'View used for ' . trans('soda-blog::general.blog') . ' ' . trans('soda-blog::general.post') . ' listings'
            ])->setLayout(soda_cms_view_path('partials.inputs.layouts.inline'))->setModel($blog) !!}
            @endpermission

            <hr />

            {!! SodaForm::toggle([
                'name'        => 'RSS feed enabled',
                'field_name'  => 'rss_enabled',
                'description' => 'Toggle Atom RSS feed usage'
            ])->setLayout(soda_cms_view_path('partials.inputs.layouts.inline'))->setModel($blog) !!}

            {!! SodaForm::text([
                'name'        => 'RSS slug',
                'field_name'  => 'rss_slug',
                'description' => 'URL to access RSS feed. Appended to base slug defined above.'
            ])->setLayout(soda_cms_view_path('partials.inputs.layouts.inline'))->setModel($blog) !!}

            @permission('develop-blog')
            {!! SodaForm::text([
                'name'        => 'RSS view',
                'field_name'  => 'rss_view',
                'description' => 'View used for RSS feed'
            ])->setLayout(soda_cms_view_path('partials.inputs.layouts.inline'))->setModel($blog) !!}

            {!! SodaForm::toggle([
                'name'        => 'Strip tags',
                'field_name'  => 'rss_strip_tags',
                'description' => 'Strip HTML from RSS output'
            ])->setLayout(soda_cms_view_path('partials.inputs.layouts.inline'))->setModel($blog) !!}
            @endpermission
        </form>
    </div>

    <div class="content-bottom">
        @include(soda_cms_view_path('partials.buttons.save'), ['submits' => '#blog-settings-form'])
    </div>
@endsection
