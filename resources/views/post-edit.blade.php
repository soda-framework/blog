@extends(soda_cms_view_path('layouts.inner'))

@section('content-heading-button')
    @include(soda_cms_view_path('partials.buttons.save'), ['submits' => '#post-form'])
    <button class="btn btn-info btn-lg" data-submits="#post-form" data-publishes>
        <i class="fa fa-eye"></i>
        <span>Save and publish</span>
    </button>
@stop

@section('content')

    @if($post->id)
        <div class="alert alert-info">
            @if($post->isPublished())
                This {{ trans('soda-blog::general.post') }} is live!
            @elseif($post->status == 1)
                This {{ trans('soda-blog::general.post') }} will not appear live until the publish date.
            @else
                This {{ trans('soda-blog::general.post') }} is in draft mode.
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
                            {!! $field = app('soda.form')->field($setting->field)->setPrefix('settings.'.$setting->field->id)->setModel($post->properties) !!}
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </form>
    <div class="content-bottom">
        @include(soda_cms_view_path('partials.buttons.save'), ['submits' => '#post-form'])
        <button class="btn btn-info btn-lg" data-submits="#post-form" data-publishes>
            <i class="fa fa-eye"></i>
            <span>Save and publish</span>
        </button>
    </div>
@endsection

@section('footer.js')
    @parent
    <script>
        (function($) {
            tinymce.PluginManager.add('blockquote', function(editor, url) {

                var checkFormatMatch = function(alignment, ctrl) {

                    // Check if the selection matches the format
                    var formatMatch = editor.formatter.match('cc_blockquote_format');

                    // Some magic to find the blockquote element from inside the selection
                    var $selectedElement = $(editor.selection.getNode());

                    if ($selectedElement.is('blockquote')) {
                        $blockquote = $selectedElement;
                    } else {
                        $blockquote = $selectedElement.closest('blockquote');
                    }

                    var alignmentMatch = $blockquote.hasClass('cc-blockquote-' + alignment);
                    var borderElementMatch = $blockquote.find('.cc-blockquote-border').length;
                    var imageElementMatch = $blockquote.hasClass('cc-blockquote-image');

                    // If all conditions are true, the button should be in its active state
                    ctrl.active( formatMatch && alignmentMatch && (borderElementMatch || imageElementMatch) );

                };

                var toggleBlockquoteFormat = function(alignment) {

                    if (!editor.formatter.match('cc_blockquote_format')) {

                        // If the blockquote format is not already applied to the element, we apply it before doing anything else.
                        editor.formatter.apply('cc_blockquote_format');

                        // Some magic to find the blockquote element from inside the selection
                        var $selectedElement = $(editor.selection.getNode());

                        if ($selectedElement.is('blockquote')) {
                            $blockquote = $selectedElement;
                        } else {
                            $blockquote = $selectedElement.closest('blockquote');
                        }

                        $blockquote.addClass('cc-blockquote-' + alignment);

                        var $img = $blockquote.find('img');
                        if ($img.length) {
                            $blockquote.addClass('cc-blockquote-image');
                        } else {
                            $blockquote.addClass('cc-blockquote-text');

                            // Check whether or not we already have a .cc-blockquote-border in the selection, in case the style was toggled off using the regular blockquote btn
                            if (!$blockquote.find('.cc-blockquote-border').length) {
                                $borderElement = $('<span>&nbsp;</span>').addClass('cc-blockquote-border');
                                $blockquote.children().last().append($borderElement);
                            }
                        }

                    } else {

                        // First we find the parent <blockquote> element
                        var $selectedElement = $(editor.selection.getNode());
                        var $blockquote = $selectedElement.closest('.cc-blockquote');

                        // Since the format is already applied, we remove the border element from inside the blockquote
                        $blockquote.find('span.cc-blockquote-border').remove();

                        // We also have to manually remove all classes that are not part of the formatter
                        $blockquote.removeClass('cc-blockquote-text cc-blockquote-image cc-blockquote-' + alignment);

                        // And then simply remove the format to get rid of the blockquote
                        editor.formatter.remove('cc_blockquote_format');
                    }

                    editor.nodeChanged(); // refresh the button state

                };

                editor.on('init', function(e) {
                    editor.formatter.register(
                            'cc_blockquote_format', {
                                block: 'blockquote',
                                classes: ['cc-blockquote'],
                                //attributes: {'class': 'cc-blockquote-%value'},  // workaround for http://www.tinymce.com/develop/bugtracker_view.php?id=6472
                                wrapper: true
                            }
                    );
                });

                editor.addButton('BlockquoteLeft', {
                    text: 'Blockquote Left',
                    icon: false,
                    onclick: function() {
                        toggleBlockquoteFormat('left');
                    },
                    onPostRender: function() {
                        var ctrl = this;
                        editor.on('NodeChange', function(e) {
                            checkFormatMatch('left', ctrl);
                        });
                    }
                });

                editor.addButton('BlockquoteCenter', {
                    text: 'Blockquote Center',
                    icon: false,
                    onclick: function() {
                        toggleBlockquoteFormat('center');
                    },
                    onPostRender: function() {
                        var ctrl = this;
                        editor.on('NodeChange', function(e) {
                            checkFormatMatch('center', ctrl);
                        });
                    }
                });

                editor.addButton('BlockquoteRight', {
                    text: 'Blockquote Right',
                    icon: false,
                    onclick: function() {
                        toggleBlockquoteFormat('right');
                    },
                    onPostRender: function() {
                        var ctrl = this;
                        editor.on('NodeChange', function(e) {
                            checkFormatMatch('right', ctrl);
                        });
                    }
                });

            });

        })(jQuery);
    </script>
@stop
