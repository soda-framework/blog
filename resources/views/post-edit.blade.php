@extends(soda_cms_view_path('layouts.inner'))

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{ route('soda.home') }}">Home</a></li>
        <li><a href="{{ route('soda.cms.blog.index') }}">{{ ucfirst(trans('soda-blog::general.blog')) }}</a></li>
        <li class="active">Editing {{ ucfirst(trans('soda-blog::general.post')) }}</li>
    </ol>
@stop

@section('head.title')
    <title>Editing {{ ucfirst(trans('soda-blog::general.post')) }}</title>
@endsection

@section('content-heading-button')
    @include(soda_cms_view_path('partials.buttons.save'), ['submits' => '#post-form'])
@stop

@include(soda_cms_view_path('partials.heading'), [
    'icon'        => 'fa fa-book',
    'title'       => 'Editing ' . ucfirst(trans('soda-blog::general.post')),
])

@section('content')

    @if($post->id)
        <div class="alert alert-info">
            @if(Carbon\Carbon::now() >= $post->published_at && $post->status == 1)
                This {{ trans('soda-blog::general.post') }} is live!
            @elseif($post->status == 0)
                This {{ trans('soda-blog::general.post') }} is in draft mode.
            @else
                This {{ trans('soda-blog::general.post') }} will not appear live until the publish date.
            @endif
        </div>
    @endif
    <form id="post-form" method="POST" action='{{ route('soda.cms.blog.save', @$post->id)}}'
          enctype="multipart/form-data">
        {!! csrf_field() !!}
        <div class="row">

            <div class="col-lg-3 pull-right col-xs-12">
                <div class="content-block">
                    {!! SodaForm::text([
                        'name'        => 'Title',
                        'field_name'  => 'name',
                    ])->setLayout(soda_cms_view_path('partials.inputs.layouts.stacked'))->setModel($post) !!}

                    {!! SodaForm::slug([
                        'name'        => 'Slug',
                        'description' => 'The URL to reach this post',
                        'field_name'  => 'slug',
                        'field_params' => [
                            'allow_external' => false,
                        ],
                    ])->setLayout(soda_cms_view_path('partials.inputs.layouts.stacked'))->setModel($post) !!}

                    {!! SodaForm::toggle([
                        'name'         => 'Published',
                        'field_name'   => 'status',
                        'value'        => Soda\Cms\Support\Constants::STATUS_LIVE,
                        'field_params' => ['checked-value' => Soda\Cms\Support\Constants::STATUS_LIVE, 'unchecked-value' => Soda\Cms\Support\Constants::STATUS_DRAFT],
                    ])->setLayout(soda_cms_view_path('partials.inputs.layouts.stacked'))->setModel($post) !!}

                    {{-- CONFIGURE TIMEZONE --}}
                    {!! SodaForm::datetime([
                        'name'         => 'Publish at',
                        'field_name'   => 'published_at',
                        'description'  => 'Note: post must be published with toggle above for date to take affect',
                        'field_params' => [
                            'timezone' => config('soda.blog.publish_timezone'),
                        ]
                    ])->setLayout(soda_cms_view_path('partials.inputs.layouts.stacked'))->setModel($post) !!}
                </div>
                <br />
            </div>
            <div class="col-lg-9 col-xs-12">
                <div class="content-block">
                    {!! SodaForm::upload([
                        'name'        => 'Featured image',
                        'field_name'  => 'featured_image',
                    ])->setModel($post) !!}

                    {!! SodaForm::textarea([
                        'name'        => 'Excerpt',
                        'field_name'  => 'excerpt',
                    ])->setModel($post) !!}

                    {!! SodaForm::tinymce([
                        'name'        => 'Post body',
                        'field_name'  => 'content',
                    ])->setModel($post) !!}

                    {!! SodaForm::combobox([
                        'name'        => 'Tags',
                        'field_name'  => 'singletags',
                        'field_params' => [
                            'multiple'   => true,
                            'array-save' => 'delimit:,',
                        ],
                    ])->setModel($post) !!}

                    @if(count($settings))
                        <hr />
                        @foreach($settings as $setting)
                            {!! SodaForm::field($setting->field)->setPrefix('setting.'.$setting->field->id) !!}
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </form>
    <div class="content-bottom">
        @include(soda_cms_view_path('partials.buttons.save'), ['submits' => '#post-form'])
    </div>
@endsection
