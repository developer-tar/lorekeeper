<ul>
    <li class="sidebar-header"><a href="{{ url('homestead/rooms') }}" class="card-link">Homestead</a></li>
    <li class="sidebar-section">
        <div class="sidebar-section-header">My Spaces</div>
        <div class="sidebar-item"><a href="{{ url('homestead/rooms') }}" class="{{ set_active('homestead/rooms*') }}">Rooms</a></div>
        <div class="sidebar-item"><a href="{{ url('homestead/houses') }}" class="{{ set_active('homestead/houses*') }}">Houses</a></div>
    </li>
</ul>
