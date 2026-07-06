@extends('homestead.layout')

@section('homestead-title') Houses @endsection

@section('homestead-content')
{!! breadcrumbs(['Homestead' => 'homestead/houses', 'Houses' => 'homestead/houses']) !!}

<h1>
    Houses
    <div class="float-right mb-3">
        @if($slots['can_create'])
            <a href="#" class="btn btn-primary create-house-button"><i class="fas fa-plus"></i> Create House</a>
        @else
            <button type="button" class="btn btn-primary" disabled title="House slot limit reached"><i class="fas fa-plus"></i> Create House</button>
        @endif
    </div>
</h1>

<p>
    Manage your outdoor houses.
    @if($slots['unlimited'])
        You have <strong>{{ $slots['used'] }}</strong> house(s). <span class="text-muted">(Staff: unlimited slots)</span>
    @else
        You are using <strong>{{ $slots['used'] }}</strong> of <strong>{{ $slots['max'] }}</strong> house slots.
    @endif
</p>

@if(!$slots['can_create'])
    <div class="alert alert-warning">
        You have reached your house slot limit. Activate a house slot item from your inventory to unlock more houses.
    </div>
@endif

@if(count($houses))
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
                @foreach($houses as $house)
                    <tr>
                        <td>{{ $house->name }}</td>
                        <td>{!! pretty_date($house->created_at) !!}</td>
                        <td class="text-right">
                            <a href="#" class="btn btn-outline-primary btn-sm edit-house-button" data-id="{{ $house->id }}">Edit</a>
                            <a href="#" class="btn btn-outline-danger btn-sm delete-house-button" data-id="{{ $house->id }}">Delete</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center mb-3">You have no houses yet.</div>
@endif

@endsection

@section('scripts')
@parent
<script>
    $(document).ready(function() {
        $('.create-house-button').on('click', function(e) {
            e.preventDefault();
            loadModal("{{ url('homestead/houses/create') }}", 'Create House');
        });

        $('.edit-house-button').on('click', function(e) {
            e.preventDefault();
            var $this = $(this);
            loadModal("{{ url('homestead/houses/edit') }}/" + $this.data('id'), 'Edit House');
        });

        $('.delete-house-button').on('click', function(e) {
            e.preventDefault();
            var $this = $(this);
            loadModal("{{ url('homestead/houses/delete') }}/" + $this.data('id'), 'Delete House');
        });
    });
</script>
@endsection
