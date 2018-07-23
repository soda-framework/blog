@extends(soda_cms_view_path('layouts.inner'))

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{ route('soda.home') }}">Home</a></li>
        <li><a href="{{ route('soda.cms.blog.index') }}">{{ ucfirst(trans('soda-blog::general.blog')) }}</a></li>
        <li class="active">Import</li>
    </ol>
@stop

@section('head.title')
    <title>{{ ucfirst(config('soda-blog.title_singular')) }} Import</title>
@endsection

@include(soda_cms_view_path('partials.heading'), [
    'icon'        => 'fa fa-book',
    'title'       => ucfirst(config('soda-blog.title_singular')) . ' Import',
])

@section('content')

    <div class="content-block">
        <h3>Tumblr Importer</h3>
        <br />
        <p>You can import posts from Tumblr. You will need a working Client ID which can be created here:<br />
            <a href='http://www.tumblr.com/oauth/apps' target='_blank'>http://www.tumblr.com/oauth/apps</a>
        </p>
        <br />
        <p>
            <a href='{{ route('soda.cms.blog.import.tumblr') }}' class='btn btn-primary' data-toggle="modal" data-target="#popup">Import From Tumblr</a>
        </p>
        {{-- TODO:::
        <div class='wordpress-import col-md-6'>
            <p>You can import posts from wordpress</p>
            <a href='{{action('ImportController@anyWordpress')}}' class='btn btn-primary' data-toggle="modal" data-target="#popup">Import From Wordpress</a>
        </div>
        --}}
    </div>
@endsection
