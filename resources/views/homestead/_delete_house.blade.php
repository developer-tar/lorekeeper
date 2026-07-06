<p>You are about to delete the house <strong>{{ $house->name }}</strong>. This will also remove its layout and furniture placements. Are you sure?</p>
<div class="text-right">
    {!! Form::open(['url' => 'homestead/houses/delete/'.$house->id]) !!}
        {!! Form::submit('Delete House', ['class' => 'btn btn-danger']) !!}
    {!! Form::close() !!}
</div>
