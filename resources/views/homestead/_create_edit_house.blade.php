{!! Form::open(['url' => $house->id ? 'homestead/houses/edit/'.$house->id : 'homestead/houses/create']) !!}
    <div class="form-group">
        {!! Form::label('name', 'House Name') !!}
        {!! Form::text('name', $house->name, ['class' => 'form-control', 'placeholder' => 'My House']) !!}
    </div>
    <div class="text-right">
        {!! Form::submit($house->id ? 'Update' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>
{!! Form::close() !!}
