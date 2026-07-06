@extends('homestead.layout')

@section('homestead-title') Rooms @endsection

@section('homestead-content')
{!! breadcrumbs(['Homestead' => 'homestead/rooms', 'Rooms' => 'homestead/rooms']) !!}

<h1>
    Rooms
    <div class="float-right mb-3">
        @if($slots['can_create'])
            <a href="#" class="btn btn-primary create-room-button"><i class="fas fa-plus"></i> Create Room</a>
        @else
            <button type="button" class="btn btn-primary" disabled title="Room slot limit reached"><i class="fas fa-plus"></i> Create Room</button>
        @endif
    </div>
</h1>

<p>
    Manage your indoor rooms.
    @if($slots['unlimited'])
        You have <strong>{{ $slots['used'] }}</strong> room(s). <span class="text-muted">(Staff: unlimited slots)</span>
    @else
        You are using <strong>{{ $slots['used'] }}</strong> of <strong>{{ $slots['max'] }}</strong> room slots.
    @endif
</p>

@if(!$slots['can_create'])
    <div class="alert alert-warning">
        You have reached your room slot limit. Activate a room slot item from your inventory to unlock more rooms.
    </div>
@endif

@if(count($rooms))
    <div class="table-responsive mb-3">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Created</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rooms as $room)
                    <tr>
                        <td>{{ $room->name }}</td>
                        <td>{!! pretty_date($room->created_at) !!}</td>
                        <td class="text-right">
                            <a href="{{ url('homestead/rooms/'.$room->id.'/editor') }}" class="btn btn-outline-success btn-sm">Editor</a>
                            <a href="#" class="btn btn-outline-primary btn-sm edit-room-button" data-id="{{ $room->id }}">Edit</a>
                            <a href="#" class="btn btn-outline-danger btn-sm delete-room-button" data-id="{{ $room->id }}">Delete</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center mb-3">You have no rooms yet.</div>
@endif

@endsection

@section('scripts')
@parent
<script>
    $(document).ready(function() {
        $('.create-room-button').on('click', function(e) {
            e.preventDefault();
            loadModal("{{ url('homestead/rooms/create') }}", 'Create Room');
        });

        $('.edit-room-button').on('click', function(e) {
            e.preventDefault();
            var $this = $(this);
            loadModal("{{ url('homestead/rooms/edit') }}/" + $this.data('id'), 'Edit Room');
        });

        $('.delete-room-button').on('click', function(e) {
            e.preventDefault();
            var $this = $(this);
            loadModal("{{ url('homestead/rooms/delete') }}/" + $this.data('id'), 'Delete Room');
        });
    });
</script>
@endsection
