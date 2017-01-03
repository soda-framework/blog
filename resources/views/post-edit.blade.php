@extends(soda_cms_view_path('layouts.inner'))

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{ route('soda.home') }}">Home</a></li>
        <li><a href="{{ route('soda.cms.blog.index') }}">{{ ucfirst(trans('soda-blog.blog-singular')) }}</a></li>
        <li class="active">Editing {{ ucfirst(trans('soda-blog.post-singular')) }}</li>
    </ol>
@stop

@section('head.title')
    <title>Editing {{ ucfirst(config('soda-blog.title_singular')) }}</title>
@endsection

@include(soda_cms_view_path('partials.heading'), [
    'icon'        => 'fa fa-book',
    'title'       => 'Editing ' . ucfirst(config('soda-blog.title_singular')),
])

@section('content')
    <div class="content-block">{!! Form::model($post, array('method' => 'POST', 'files'=>true, 'class'=>'main-form', 'action' => array('\Bootleg\Blog\PostController@postView', @$post->id))) !!}
        <ul>
            <li>{{ strtoupper(config('bootleg.blog.post_singular', 'post')) }} ID: {{$post->id}}</li>

            <li class="form-group">
                {!! Form::label('name', 'Title:') !!}
                {!! Form::input('name', 'name', null, array('class'=>'blog-name form-control')) !!}
            </li>

            <li class="form-group">
                {!! Form::label('slug', 'Slug:') !!} <button class='btn btn-default btn-xs js-generate-slug'>generate</button>
                <div class="input-group">
                    <?php
                    $niceFullSlug = "http://".Soda::getApplicationUrl()->domain;
                    $niceFullSlug .= "/".$blog->slug;
                    ?>
                    <span class="input-group-addon">{{$niceFullSlug}}</span>
                    {!! Form::input('slug', 'slug', null, array('class'=>'form-control js-slug')) !!}
                </div>
            </li>

            <li class="form-group">
                {!! Form::label('published_at', 'Publish At:') !!} <button class='btn btn-default btn-xs js-now'>now</button>
                <div class='input-group datetimepicker'>
                    {!! Form::input('published_at', 'published_at', @$post->published_at ? @$post->published_at->setTimezone(config('soda.blog.publish_timezone')) : null, array('class'=>'form-control js-publish-at')) !!}
                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </li>

            <li class="form-group">
                {!! Form::label('singletags', 'Tags:') !!}
                {!! Form::input('singletags', 'singletags', null, array('data-role'=>'tagsinput', 'class'=>'form-control tagsinput')) !!}
            </li>

            <li class="form-group">

                <ul class='nav nav-tabs' role='tablist'>
                    <?php
                    $contentTypes = Event::fire('blog.post.types', array($post));
                    ?>
                    <li role='presentation' class='active'><a class='js-type-tab' href='#content-tab' aria-controls="content-tab" role="tab" data-toggle="tab">Content</a></li>
                    @foreach($contentTypes as $contentType)
                        <li role='presentation' ><a class='js-type-tab' aria-controls="{{$contentType['title']}}-tab" role="tab" data-toggle="tab" href="#{{$contentType['title']}}-tab" data-url='{{$contentType['location']}}'>{{$contentType['title']}}</a></li>
                    @endforeach
                </ul>
                <div class='tab-content'>
                    <div class='tab-pane active' id='content-tab'>
                        {!! Form::textarea("content", $post->content, array('class'=>'tinymce content')) !!}
                        <script>
                            var inline_image = "";
                            $(function() {

                                tinymce.PluginManager.add('uploadImage', function(editor, url) {
                                    // Add a button that opens a window
                                    editor.addButton('upload', {
                                        text: 'Upload Image',
                                        icon: 'image',
                                        onclick: function() {
                                            // Open window
                                            editor.windowManager.open({
                                                title: 'Upload Image',
                                                url: '/cms/content/inline-upload',
                                                //    body: [
                                                //        {type: 'textbox', name: 'title', label: 'Title'}
                                                //    ],
                                                buttons: [{
                                                    text: 'Close',
                                                    onclick: 'close'
                                                },
                                                    {
                                                        text:'OK',
                                                        onclick: function(e){
                                                            console.log(editor);
                                                            console.log(url);
                                                            console.log(e);
                                                            editor.execCommand('mceInsertContent', false, '<img src="' + inline_image +'" />');
                                                            top.tinymce.activeEditor.windowManager.close();
                                                        }
                                                    }],
                                            });
                                        }
                                    });
                                });
                                tinymce.remove(); //purge any existing instances of this.
                                tinymce.baseURL = '/vendor/bootleg/cms/components/tinymce-builded/js/tinymce';
                                tinymce.init({
                                    selector:'textarea.tinymce.content',
                                    plugins: ["link", "code", "hr", "image", "table", "media", "uploadImage"],
                                    toolbar:"undo redo | styleselect | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image upload",
                                    relative_urls: false
                                });
                            });
                        </script>
                    </div>
                    @foreach($contentTypes as $contentType)
                        <div class='tab-pane' id='{{$contentType['title']}}-tab'>

                        </div>
                    @endforeach
                </div>
            </li>

            <li class="form-group">
                {!! Form::label('excerpt', 'Excerpt:') !!}
                {!! Form::textarea('excerpt', null, array('class'=>'excerpt form-control')) !!}
            </li>

            <li class="form-group">
                <?php
                $fakeSetting = new stdClass();
                $fakeSetting->field_type = 'upload';
                $fakeSetting->name = 'Featured Image';
                $fakeSetting->value = $post->featured_image;
                $fakeSetting->id = 0;
                $fakeSetting->content_id = 0;
                $fakeSetting->section = 'content';
                $fakeSetting->field_parameters = Contentsetting::DEFAULT_UPLOAD_JSON;
                $fakeSettingArray = array(0=>$fakeSetting);
                ?>
                @include('cms::contents.input_types.'.$fakeSettingArray[0]->field_type, array('setting'=>$fakeSettingArray))
            </li>

            <li class="form-group">
                <label>Status:</label>
                <div class="radio">
                    <label>

                        {!! Form::radio('status_id','0','') !!}
                        Draft
                    </label>
                </div>
                <div class="radio">
                    <label>
                        {!!Form::radio('status_id','1','') !!}
                        Published
                    </label>
                </div>
            </li>

            @if(count($settings))
                <li>
                    <h2>Extra Settings</h2>
                </li>
                @foreach($settings as $setting)
                    <li class="form-group">
                        @include('cms::contents.input_types.'.$setting[0]->field_type, array('setting'=>$setting))
                    </li>
                @endforeach
            @endif

            <li class="form-group">
                <div class='btn-group btn-group-lg'>

                    {!! Form::submit(@$post->id?'Update':'Create', array('class' => 'btn btn-success ', 'onclick' => '$(".main-form").attr("target", "");')) !!}
                    @if(!@$post->id)
                        {!! Form::submit('Preview', array('name'=>'preview', 'class'=>'btn btn-primary', 'onclick' => '$(".main-form").attr("target", "_blank");'))!!}
                    @endif
                    {!! link_to_action('\Bootleg\Blog\PostController@getView', 'Cancel', @$post->id, array('class' => 'btn btn-danger ')) !!}
                </div>
            </li>
        </ul>
        </form>


        <script type="text/javascript">
            $(function () {
                $('.datetimepicker').datetimepicker({
                    'format':'YYYY-MM-DD H:mm:SS',
                    'useCurrent':true
                });

                //handle tabs that can come from events.
                $('a.js-type-tab').on('shown.bs.tab', function (e) {
                    //e.target // newly activated tab
                    $me = $(this);
                    if($(e.target).data('url')){
                        $.ajax($(e.target).data('url')).done( function(data){
                            console.log($me.attr('href'));
                            $($me.attr('href')).html(data);
                        });
                    }
                })

                $('.js-now').click(function(e){
                    e.preventDefault();
                    $('.js-publish-at').val(moment().format('YYYY-MM-DD HH:mm:ss'));
                });


                //generate slug from name.
                $('.js-generate-slug').click(function(e){
                    e.preventDefault();
                    var str = $('.blog-name').val().replace(/ /g, '-');
                    str = '/'+str.replace(/[^a-zA-Z0-9-_]/g, '');
                    $('.js-slug').val(str.toLowerCase());
                });
            });
        </script>
        <script type="text/javascript">

            $('input#slug').on('keyup', function (e) {
                console.log('here');
                if (this.value.match(/[^a-zA-Z0-9-_\/]/g)) {
                    this.value = this.value.replace(/[^a-zA-Z0-9-_\/]/g, '');
                }
            });

        </script>
    </div>
@endsection
