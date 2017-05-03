@extends(soda_cms_view_path('layouts.inner'))

@section('content-heading-button')
    @include(soda_cms_view_path('partials.buttons.create'), ['url' => route('soda.cms.blog.create')])
@stop

@section('content')
    <div class="content-top">
        <div class='row'>
            <form method='GET'>
                <div class="col-md-6">
                    <label for="field_search">Search</label>
                    <div class="input-group">
                        <input name="search" id="field_search" type="text" class="form-control field_search search" value="{{ Request::query('search') }}">

                        <span class="input-group-btn">
                            <button class="btn btn-default"><span class='glyphicon glyphicon-search'></span></button>
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="field_filter_value">Status</label>
                    <div>
                        <select name="status" class="form-control" id="field_filter_value">
                            <option value="" {{ Request::query('status') === "" ? "selected" : "" }}>All</option>
                            <option value="1" {{ Request::query('status') == "1" ? "selected" : "" }}>Published</option>
                            <option value="0" {{ Request::query('status') == "0" ? "selected" : "" }}>Draft</option>
                            <option value="2" {{ Request::query('status') == "2" ? "selected" : "" }}>Pending publish</option>
                        </select>
                    </div>
                </div>
            </form><!-- /input-group -->
        </div>
    </div>

    @if ($posts->count())
        <div class="content-block">
            <table class="table table-striped middle">
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
                        <th width="140">
                            <a href='?sort=status&direction={{ $dir }}'>
                                <span class='glyphicon glyphicon-chevron-{{ Input::get('sort') == 'status' ? $d : '' }}'></span>
                                Status
                            </a>
                        </th>
                        <th width="250">
                            <a href='?sort=published_at&direction={{ $dir }}'>
                                <span class='glyphicon glyphicon-chevron-{{ Input::get('sort') == 'published_at' ? $d : '' }}'></span>
                                Date Published
                            </a>
                        </th>
                        <th width="100" class="text-right">Actions</th>
                </tr>
                </thead>

                <tbody class="sortable" data-entityname="soda-blog-post">
                @foreach ($posts as $post)
                    <tr data-itemId="{{ $post->id }}">
                            @if(!Input::get('sort'))
                            <td class="sortable-handle text-center">
                                <img src="/soda/cms/img/drag-dots.gif" />
                            </td>
                            @endif
                            <td>
                                <span style="margin-left:5px">{{ $post->name }}</span>
                            </td>
                            <td>
                                <span class="text-monospaced" style="font-size:12px">/{{ trim($blog->slug . $post->slug, '/') }}</span>
                            </td>
                            <td>
                                <span class="{{ $post->isPublished() == \Soda\Cms\Foundation\Constants::STATUS_DRAFT ? 'inactive' : 'active' }}-circle"></span> <span>{{ $post->isPublished() == \Soda\Cms\Foundation\Constants::STATUS_DRAFT ? 'Draft' : 'Published' }}</span>
                            </td>
                            <td>
                                {{ @$post->published_at ? @$post->published_at->setTimezone(config('soda.blog.publish_timezone'))->toDayDateTimeString() : '' }}
                            </td>
                            <td>
                                <div class="option-buttons pull-right">
                                    <div style="display:inline-block;position:relative;">
                                        <a href="#" class="btn btn-info option-more" data-toggle="dropdown" aria-expanded="false">
                                            <i class="fa fa-ellipsis-v"></i>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a href="{{ route('soda.cms.blog.edit', $post->id) }}">Edit {{ ucfirst(trans('soda-blog::general.post')) }}</a>
                                            </li>
                                            <li>
                                                <a href="{{ URL::to($blog->slug . '/' . trim($post->slug, '/')) }}" target="_blank" data-tree-link>View {{ ucfirst(trans('soda-blog::general.post')) }}</a>
                                            </li>
                                            <li class="divider"></li>
                                            <li class="warning">
                                                <a data-post-delete="{{ route('soda.cms.blog.delete', $post->id) }}" href="#">Delete</a>
                                            </li><!--v-if-->
                                        </ul>
                                    </div>
                                </div>
                            </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="content-bottom">
            <div class="clearfix">
                <div class="pull-left">
                    {!! $posts->appends(Request::only('status', 'search'))->render() !!}
                </div>
                <div class="pull-right">
                    @include(soda_cms_view_path('partials.buttons.create'), ['url' => route('soda.cms.blog.create')])
                </div>
            </div>
        </div>

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
    @else
        <div class="content-block">
            There are no {{ trans('soda-blog::general.posts') }} to show
        </div>

        <div class="content-bottom">
            @include(soda_cms_view_path('partials.buttons.create'), ['url' => route('soda.cms.blog.create')])
        </div>
    @endif
@endsection

@section('footer.js')
    @parent
    <link href="/soda/cms/css/extra.min.css" type="text/css"/>
    <script src="/soda/cms/js/extra.min.js"></script>
    <script>
        $(document).on('ready', function () {
            $('select.filter').change(function (e) {
                e.preventDefault();
                $(this).closest('form').submit();
            });

            $('[data-post-delete]').on('click', function () {
                $('#confirm-delete form').attr('action', $(this).data('post-delete'));
                $('#confirm-delete').modal('show');
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
@stop
