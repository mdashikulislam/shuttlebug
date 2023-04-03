@if ( count($schools) == 0 )
    <p class="alert alert-info">No match found</p>
@else
    <div class="list-group">
        @foreach ( $schools as $school )
            <a class="list-group-item list-group-item-action flex-column align-items-start" href="{!! url('office/schools/edit/'.$school->id) !!}">
                <div class="d-flex w-100 justify-content-between">
                    <strong>{{ $school->name }}</strong>
                    <span>{{ $school->id }}</span>
                </div>
                <div class="d-flex w-100 justify-content-between">
                    <span>{{ $school->address }} {{ $school->phone }}</span>
                    <span>{{ $school->status }}</span>
                </div>
            </a>
        @endforeach
    </div>
@endif