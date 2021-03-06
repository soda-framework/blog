<?php
    $blogPostOrder = array_keys(config('soda.blog.default_sort'))[0];
    $blogPostsOrderable =  \Soda\Blog\Models\Post::getSortableField() == $blogPostOrder;
?>

@extends(soda_cms_view_path('layouts.inner'))

@section('content-heading-button')
    @include(soda_cms_view_path('partials.buttons.create'), ['url' => route('soda.cms.blog.create')])
@stop

@section('content')
    <div class="content-top">
        <div class='row'>
            <form method='GET' class="form-inline">

                <div class="col-xs-12 col-sm-10">
                    <div class="form-group" style="width:100%">
                        <div class="input-group input-group-lg" style="width:100%">
                            <input type="text" name="search" class="form-control form-control-alt has-floating-addon" value="{{ Request::input('search') }}" placeholder="Search blog posts..." />
                            <div class="input-group-floating-addon"><i class="mdi mdi-magnify"></i></div>
                        <span class="input-group-btn">
                            <button class="btn btn-default">Search</button>
                        </span>
                        </div>
                    </div>
                </div>

                <div class="col-xs-12 col-sm-2">
                    <div class="pull-right">
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control">
                                <option value="" {{ Request::input('status') === "" ? "selected" : "" }}>All</option>
                                <option value="1" {{ Request::input('status') == "1" ? "selected" : "" }}>Published</option>
                                <option value="0" {{ Request::input('status') == "0" ? "selected" : "" }}>Draft</option>
                                <option value="2" {{ Request::input('status') == "2" ? "selected" : "" }}>Pending publish</option>
                            </select>
                        </div>
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
                        @if($blogPostsOrderable && !Input::get('sort'))
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
                            @if($blogPostsOrderable && !Input::get('sort'))
                            <td class="sortable-handle text-center">
                                <img src="/soda/cms/img/drag-dots.gif" />
                            </td>
                            @endif
                            <td>
                                <span style="margin-left:5px">{{ $post->name }}</span>
                            </td>
                            <td>
                                <span class="text-monospaced" style="font-size:12px">/{{ trim($blog->getSetting('slug') . $post->slug, '/') }}</span>
                            </td>
                            <td>
                                @if($post->isPublished())
                                    <span class="active-circle"></span> <span>Published</span>
                                @else
                                    <span class="inactive-circle"></span> <span>{{ $post->status == \Soda\Cms\Foundation\Constants::STATUS_DRAFT ? 'Draft' : 'Pending' }}</span>
                                @endif
                            </td>
                            <td>
                                @if($post->published_at)
                                    {{ @$post->published_at ? @$post->published_at->setTimezone(config('soda.cms.publish_timezone', config('soda.blog.publish_timezone', 'UTC')))->toDayDateTimeString() : '' }}
                                @else
                                    -
                                @endif
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
                                            @if($post->isPublished() || Session::get("soda.draft_mode") == true)
                                            <li>
                                                <a href="{{ URL::to(trim($blog->getSetting('slug') . $post->slug, '/')) }}" target="_blank" data-tree-link>View {{ ucfirst(trans('soda-blog::general.post')) }}</a>
                                            </li>
                                            @endif
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
    @else
        <div class="content-block">
            There are no {{ trans('soda-blog::general.posts') }} to show
        </div>

        <div class="content-bottom">
            @include(soda_cms_view_path('partials.buttons.create'), ['url' => route('soda.cms.blog.create')])
        </div>
    @endif
@endsection

@section('modals')
    @parent
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
@stop

@section('footer.js')
    @parent
    <script src="/soda/cms/js/forms/sortable.js"></script>
    <script>
        $(function () {
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
