@extends(soda_cms_view_path('layouts.inner'))

@section('content-heading-button')
    @include(soda_cms_view_path('partials.buttons.save'), ['submits' => '#post-form'])
    <button class="btn btn-success btn-lg" data-submits="#post-form" data-publishes>
        <i class="fa fa-eye"></i>
        <span>Save and publish</span>
    </button>
@stop

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
    <form id="post-form" method="POST" action="{{ route('soda.cms.blog.save', @$post->id) }}" enctype="multipart/form-data">
        {!! csrf_field() !!}
        <div class="row">

            <div class="col-xs-12">
                <div class="content-block">
                    {!! app('soda.form')->text([
                        'name'         => 'Title',
                        'field_name'   => 'name',
                        'field_params' => config('soda.blog.field_params.name'),
                    ])->setLayout(soda_cms_view_path('partials.inputs.layouts.stacked'))->setModel($post) !!}

                    {!! app('soda.form')->slug([
                        'name'        => 'Slug',
                        'description' => 'The URL to reach this ' . trans('soda-blog::general.post'),
                        'field_name'  => 'slug',
                        'field_params' => [
                            'allow_external' => false,
                        ],
                    ])->setLayout(soda_cms_view_path('partials.inputs.layouts.stacked'))->setModel($post) !!}

                    {!! app('soda.form')->toggle([
                        'name'         => 'Published',
                        'field_name'   => 'status',
                        'value'        => Soda\Cms\Foundation\Constants::STATUS_LIVE,
                        'field_params' => ['checked-value' => Soda\Cms\Foundation\Constants::STATUS_LIVE, 'unchecked-value' => Soda\Cms\Foundation\Constants::STATUS_DRAFT],
                    ])->setModel($post) !!}
                    
                    {!! app('soda.form')->datetime([
                        'name'         => 'Publish at',
                        'field_name'   => 'published_at',
                        'description'  => 'Note: ' . trans('soda-blog::general.post') . ' must be published with toggle above for date to take affect',
                        'field_params' => [
                            'timezone' => config('soda.blog.publish_timezone'),
                        ]
                    ])->setModel($post) !!}

                    <hr />

                    {!! app('soda.form')->upload([
                        'name'         => 'Featured image',
                        'field_name'   => 'featured_image',
                        'field_params' => config('soda.blog.field_params.featured_image'),
                    ])->setModel($post) !!}

                    {!! app('soda.form')->tinymce([
                        'name'         => ucfirst(trans('soda-blog::general.post')) . ' body',
                        'field_name'   => 'content',
                        'field_params' => config('soda.blog.field_params.content'),
                    ])->setModel($post) !!}

                    {!! app('soda.form')->combobox([
                        'name'        => 'Tags',
                        'field_name'  => 'singletags',
                        'field_params' => array_merge([
                            'multiple'   => true,
                            'array-save' => 'delimit:,',
                        ], config('soda.blog.field_params.featured_image')),
                        'description' => 'A list of keywords describing the ' . trans('soda-blog::general.post') . '. Press enter to complete each keyword.'
                    ])->setModel($post) !!}

                    @if(count($settings))
                        <hr />
                        @foreach($settings as $setting)
                            {!! app('soda.form')->field($setting->field)->setPrefix('setting.'.$setting->field->id)->setModel($post) !!}
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </form>
    <div class="content-bottom">
        @include(soda_cms_view_path('partials.buttons.save'), ['submits' => '#post-form'])
        <button class="btn btn-success btn-lg" data-submits="#post-form" data-publishes>
            <i class="fa fa-eye"></i>
            <span>Save and publish</span>
        </button>
    </div>
@endsection
