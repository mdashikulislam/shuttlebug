@if ( count($vehicles) == 0 )
    <p class="alert alert-info">No match found</p>
@else
    <div class="list-group">
        @foreach ( $vehicles as $vehicle )
            <a class="list-group-item list-group-item-action flex-column align-items-start" href="{!! url('office/vehicles/edit/'.$vehicle->id) !!}">
                <div class="d-flex w-100 justify-content-between">
                    <strong>{{ $vehicle->model }}</strong>
                    <span>{{ $vehicle->id }}</span>
                </div>
                <div class="d-flex w-100 justify-content-between">
                    <span>seats: {{ $vehicle->seats }}</span><br>
                    <span>{{ $vehicle->status }}</span>
                </div>
            </a>
        @endforeach
    </div>
@endif