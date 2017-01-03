@extends(soda_cms_view_path('layouts.inner'))

@section('breadcrumb')
    <ol class="breadcrumb">
        <li><a href="{{ route('soda.home') }}">Home</a></li>
        <li><a href="{{ route('soda.cms.blog.index') }}">{{ ucfirst(trans('soda-blog.blog-singular')) }}</a></li>
        <li class="active">{{ ucfirst(trans('soda-blog.post-plural')) }}</li>
    </ol>
@stop

@section('head.title')
    <title>{{ ucfirst(trans('soda-blog.blog-singular')) }} {{ ucfirst(trans('soda-blog.post-plural')) }}</title>
@endsection

@include(soda_cms_view_path('partials.heading'), [
    'icon'  => 'fa fa-book',
    'title' => ucfirst(trans('soda-blog.blog-singular')) . ucfirst(trans('soda-blog.post-plural')),
])

@section('content')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.css" type="text/css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.0/jquery-ui.min.js"></script>
    <div class="content-top">
        <div class='row'>
            <div class="form-group col-md-6">
                <form class="input-group" method='GET'>
                    {!! Form::label('search', 'Search:', array('class'=>'sr-only')) !!}
                    {!! Form::input('search', 'search', null, array('placeholder'=>'Search for...', 'class'=>'form-control')) !!}

                    <span class="input-group-btn">
                        <button class="btn btn-default"><span class='glyphicon glyphicon-search'></span></button>
                    </span>
                </form><!-- /input-group -->
            </div>
            <div class="form-group col-md-6">
                <form method='GET'>
                    <input type='hidden' name='filter' value='status'/>
                    {!! Form::select('filter_value', [
                        '' => 'all',
                        'denied' => 'denied',
                        'complete' => 'complete',
                        'pending' => 'pending'
                    ], @Input::get('filter_value'), array('class'=>'filter form-control')) !!}
                </form>
            </div>
        </div>
    </div>

    <div class="content-block">
        @if ($posts->count())
            <table class="table table-striped table-bordered">
                <thead>
                <tr>
                        @if(!Input::get('sort'))
                        <th width="50">Sort</th>
                        @endif
                        <?php
                        if (Input::get('sort') && (Input::get('direction') == 'asc' || Input::get('direction') == '' || !Input::get('direction'))) {
                            $d = 'down';
                        } elseif (Input::get('sort') && (Input::get('direction') == 'desc')) {
                            $d = 'up';
                        }
                        //figure out swapped urls for sorting.
                        $dir = Input::get('direction') == 'desc' ? 'asc' : 'desc';
                        ?>
                        <th>
                            <a href='?sort=name&direction={{ $dir }}'>
                                <span class='glyphicon glyphicon-chevron-{{ Input::get('sort') == 'name' ? $d : '' }}'></span>
                                Name
                            </a>
                        </th>
                        <th>
                            <a href='?sort=slug&direction={{ $dir }}'>
                                <span class='glyphicon glyphicon-chevron-{{ Input::get('sort') == 'slug' ? $d : '' }}'></span>
                                Slug
                            </a>
                        </th>
                        <th width="250">
                            <a href='?sort=published_at&direction={{ $dir }}'>
                                <span class='glyphicon glyphicon-chevron-{{ Input::get('sort') == 'published_at' ? $d : '' }}'></span>
                                Date Published
                            </a>
                        </th>
                        <th width="200">Actions</th>
                </tr>
                </thead>

                <tbody class="sortable" data-entityname="bootleg-blog-post">
                @foreach ($posts as $post)
                    <tr data-itemId="{{ $post->id }}">
                            @if(!Input::get('sort'))
                            <td class="sortable-handle text-center">
                                <span class="glyphicon glyphicon-menu-hamburger"></span>
                            </td>
                            @endif
                            <td>{{ $post->name }}</td>
                            <td>/{{ $blog->slug . $post->slug }}</td>
                            <td>{{ @$post->published_at ? @$post->published_at->setTimezone('Australia/Sydney')->toDayDateTimeString() : '' }}</td>
                            <td>
                                <a href="{{ route('soda.cms.blog.edit', $post->id) }}"
                                   class="btn btn-warning">View {{ ucfirst(trans('soda-blog.post-singular')) }}</a>
                                <a href="#" class="btn btn-danger post-delete-button" data-toggle="modal"
                                   data-target="#confirm-delete"
                                   data-action="{{ route('soda.cms.blog.delete', $post->id) }}">Delete</a>
                            </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            {!! $posts->render() !!}

            <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            Confirmation
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete this item? This cannot be undone.
                        </div>
                        <div class="modal-footer">
                            <form method="POST">
                                <input name="_token" type="hidden" value="{{ csrf_token() }}">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                                <button class="btn btn-danger btn-ok">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                $(document).on('ready', function () {
                    $('select.filter').change(function (e) {
                        e.preventDefault();
                        $(this).closest('form').submit();
                    });

                    $('.post-delete-button').on('click', function () {
                        $('#confirm-delete form').attr('action', $(this).data('action'));
                    });

                    var $sortableTable = $('.sortable');
                    if ($sortableTable.length > 0) {
                        $sortableTable.sortable({
                            handle: '.sortable-handle',
                            axis: 'y',
                            update: function (a, b) {

                                var entityName = $(this).data('entityname');
                                var $sorted = b.item;

                                var $previous = $sorted.prev();
                                var $next = $sorted.next();

                                if ($previous.length > 0) {
                                    changePosition({
                                        parentId: $sorted.data('parentid'),
                                        type: 'moveAfter',
                                        entityName: entityName,
                                        id: $sorted.data('itemid'),
                                        positionEntityId: $previous.data('itemid'),
                                        '_token': '{{ csrf_token() }}'
                                    });
                                } else if ($next.length > 0) {
                                    changePosition({
                                        parentId: $sorted.data('parentid'),
                                        type: 'moveBefore',
                                        entityName: entityName,
                                        id: $sorted.data('itemid'),
                                        positionEntityId: $next.data('itemid'),
                                        '_token': '{{ csrf_token() }}'
                                    });
                                } else {
                                    console.error('Something went wrong!');
                                }
                            },
                            cursor: "move"
                        });
                    }
                });

                /**
                 *
                 * @param type string 'insertAfter' or 'insertBefore'
                 * @param entityName
                 * @param id
                 * @param positionId
                 */
                var changePosition = function (requestData) {
                    $.ajax({
                        'url': '{{ route('soda.cms.blog.sort') }}',
                        'type': 'POST',
                        'data': requestData,
                        'success': function (data) {
                            if (data.success) {
                                console.log('Saved!');
                            } else {
                                console.error(data.errors);
                            }
                        },
                        'error': function () {
                            console.error('Something wrong!');
                        }
                    });
                };
            </script>
        @else
            There are no {{ trans('soda-blog.post-plural') }} to show
        @endif
    </div>
@endsection
