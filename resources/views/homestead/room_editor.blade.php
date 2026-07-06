@extends('homestead.editor_layout')

@section('editor-title') {{ $room->name }} @endsection

@section('editor-content')
<div class="homestead-editor" id="homesteadEditorApp">
    <div class="homestead-editor-toolbar d-flex align-items-center justify-content-between flex-wrap">
        <div class="homestead-editor-toolbar-title mb-2 mb-md-0">
            <h1 class="h4 mb-0">{{ $room->name }}</h1>
            <small class="text-muted d-block">Room Editor</small>
            <small class="text-muted">Click furniture to place. Drag to move. Save to keep your layout.</small>
        </div>
        <div class="homestead-editor-toolbar-actions d-flex flex-wrap align-items-center">
            <div class="homestead-editor-selection-controls btn-group btn-group-sm mr-2 mb-2 d-none" id="homesteadEditorControls">
                <button type="button" class="btn btn-outline-secondary" data-editor-action="backward" title="Send backward">
                    <i class="fas fa-arrow-down"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary" data-editor-action="forward" title="Bring forward">
                    <i class="fas fa-arrow-up"></i>
                </button>
                <button type="button" class="btn btn-outline-danger" data-editor-action="remove" title="Remove item">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm d-lg-none mr-2 mb-2" id="toggleInventoryButton">
                <i class="fas fa-box-open"></i> Inventory
            </button>
            <a href="{{ $exitUrl }}" class="btn btn-outline-secondary btn-sm mr-2 mb-2">
                <i class="fas fa-times"></i> Exit
            </a>
            {!! Form::open(['url' => 'homestead/rooms/'.$room->id.'/editor', 'id' => 'homesteadEditorSaveForm', 'class' => 'd-inline mb-2']) !!}
                <input type="hidden" name="placements" id="homesteadEditorPlacementsInput" value="">
                {!! Form::submit('Save', ['class' => 'btn btn-primary btn-sm', 'id' => 'homesteadEditorSaveButton']) !!}
            {!! Form::close() !!}
        </div>
    </div>

    <div class="homestead-editor-body row no-gutters">
        <div class="col-lg-9 homestead-editor-canvas-col">
            <div class="homestead-editor-canvas" id="homesteadEditorCanvasWrap">
                <div class="homestead-editor-canvas-container" id="homesteadEditorCanvasContainer">
                    <div class="homestead-editor-canvas-stage" id="homesteadEditorCanvasStage">
                        <div class="homestead-editor-canvas-room-bg"></div>
                        <div class="homestead-editor-canvas-items" id="homesteadEditorCanvasItems"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 homestead-editor-inventory-col" id="editorInventoryPanel">
            <div class="homestead-editor-inventory">
                <div class="homestead-editor-inventory-header">
                    <h2 class="h6 mb-0 text-uppercase">Inventory</h2>
                </div>
                <div class="homestead-editor-inventory-body" id="homesteadEditorInventory">
                    @include('homestead._editor_inventory')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('editor-scripts')
<script src="{{ asset('js/homestead-room-editor.js') }}"></script>
<script>
    $(document).ready(function() {
        $('body').addClass('homestead-editor-active');
        $('#sidebar').hide();
        $('.site-mobile-header').hide();
        $('.main-content').removeClass('col-lg-8').addClass('col-lg-10 offset-lg-1');
        $('.site-footer').hide();

        var editor = new HomesteadRoomEditor({
            canvasWidth: {{ $canvasWidth }},
            canvasHeight: {{ $canvasHeight }},
            catalog: @json($editorCatalog),
            initialPlacements: @json($initialPlacements),
            canvasWrapSelector: '#homesteadEditorCanvasWrap',
            canvasContainerSelector: '#homesteadEditorCanvasContainer',
            canvasStageSelector: '#homesteadEditorCanvasStage',
            canvasItemsSelector: '#homesteadEditorCanvasItems',
            controlsSelector: '#homesteadEditorControls',
            inventorySelector: '#homesteadEditorInventory',
        });

        window.homesteadRoomEditor = editor;

        $('#homesteadEditorSaveForm').on('submit', function() {
            $('#homesteadEditorPlacementsInput').val(JSON.stringify(editor.getPlacementsPayload()));
        });

        var $panel = $('#editorInventoryPanel');
        var $toggle = $('#toggleInventoryButton');

        $toggle.on('click', function() {
            $panel.toggleClass('is-open');
            $(this).toggleClass('active');
        });

        $(document).on('click', function(e) {
            if ($(window).width() >= 992) return;
            if (!$panel.hasClass('is-open')) return;
            if ($panel.is(e.target) || $panel.has(e.target).length) return;
            if ($toggle.is(e.target) || $toggle.has(e.target).length) return;
            $panel.removeClass('is-open');
            $toggle.removeClass('active');
        });
    });
</script>
@endsection
