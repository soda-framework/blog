<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <h4 class="modal-title">Tumblr Settings</h4>
</div>
<form action='{{ route('soda.cms.blog.import.tumblr.save') }}' class='main-form' method="POST">
    {!! csrf_field() !!}
    <div class="modal-body">
        <p>
            In order for the tumblr import to work you'll need to enter in the details below:
        </p>
        <ul>
            <li class="form-group">
                {!! Form::label('tumblr_api_key', 'Tumblr Client ID:') !!}
                {!! Form::input('tumblr_api_key', 'tumblr_api_key', null, ['class' => 'form-control']) !!}
            </li>
            <li class="checkbox">
                <label>
                    {!! Form::checkbox('rehost', 'rehost', false) !!}
                    Rehost Images
                </label>
            </li>
            <li class="form-group">
                {!! Form::label('tumblr_url', 'Tumblr Url:') !!}
                {!! Form::input('tumblr_url', 'tumblr_url', null, ['class' => 'form-control']) !!}
            </li>
            <li class="form-group">
                {!! Form::label('offset', 'Offset:') !!}
                {!! Form::input('offset', 'offset', null, ['class' => 'form-control']) !!}
            </li>
        </ul>
        <div class="alert alert-warning alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Warning!</strong> This can take a long time (hours) to pull in posts and re-host images.
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button class="btn btn-primary">Go</button>
    </div>
</form>
