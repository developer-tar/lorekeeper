{!! Form::open(['url' => $room->id ? 'homestead/rooms/edit/'.$room->id : 'homestead/rooms/create']) !!}
    <div class="form-group">
        {!! Form::label('name', 'Room Name') !!}
        {!! Form::text('name', $room->name, ['class' => 'form-control', 'placeholder' => 'My Room']) !!}
    </div>
    <div class="text-right">
        {!! Form::submit($room->id ? 'Update' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>
{!! Form::close() !!}
