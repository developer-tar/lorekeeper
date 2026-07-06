<p>You are about to delete the room <strong>{{ $room->name }}</strong>. This will also remove its layout and furniture placements. Are you sure?</p>
<div class="text-right">
    {!! Form::open(['url' => 'homestead/rooms/delete/'.$room->id]) !!}
        {!! Form::submit('Delete Room', ['class' => 'btn btn-danger']) !!}
    {!! Form::close() !!}
</div>
