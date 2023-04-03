@if ( is_null($passenger) )
    <div class="text-center mt-5"><h2>Passenger not found.</h2></div>
@else
    <div class="row mt-5">
        <div class="col-lg-6">
            <div class="card text-white bg-dark mb-5">
                <div class="card-body">
                    @if ( file_exists(public_path().'/images/passengers/'.$passenger->id.'.jpg') )
                        <img class="d-none d-sm-block float-left mr-3 mr-md-5" src="{!! asset('/images/passengers/'.$passenger->id.'.jpg') !!}" alt="passenger">
                        <img class="d-block d-sm-none float-left mr-3 mr-md-5" src="{!! asset('/images/passengers/'.$passenger->id.'.jpg') !!}" width="120px" alt="passenger">
                    @else
                        <img class="d-none d-sm-block float-left mr-3 mr-md-5" src="{!! asset('/images/passengers/000000.jpg') !!}" alt="no photo">
                        <img class="d-block d-sm-none float-left mr-3 mr-md-5" src="{!! asset('/images/passengers/000000.jpg') !!}" width="100px" alt="no photo">
                    @endif
                    <h2>{{ $passenger->name }}</h2>
                    @if ( Carbon\Carbon::parse($passenger->dob)->isBirthday(now()) )
                        <h5 style="color: #ffff00">{{ $passenger->first_name }}'s Birthday Today !</h5>
                    @endif
                    <p>Id: {{ $passenger->id }}</p>
                    <p>Age: {{ $passenger->dob > '0000-00-00' ? Carbon\Carbon::parse($passenger->dob)->age : '?' }}</p>
                    <p>Phone: {{ $passenger->phone }}</p>
                    <p>Medical: {{ $passenger->medical }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card mb-5">
                <div class="card-body">
                    <h4>Contacts</h4>
                    <div class="row">
                        <div class="col-md mt-3">
                            <p><strong>{{ $passenger->user->relation }}</strong>: {{ $passenger->user->name }}</p>
                            @if ( !is_null($passenger->user->guardians) )
                                @foreach ( $passenger->user->guardians as $guardian )
                                    <p><strong>{{ $guardian->relation }}</strong>: {{ $guardian->name }}</p>
                                @endforeach
                            @endif
                        </div>
                        <div class="col-md mt-3">
                            <p>{{ $passenger->user->mobile }}</p>
                            @if ( !is_null($passenger->user->guardians) )
                                @foreach ( $passenger->user->guardians as $guardian )
                                    <p>{{ $guardian->phone }}</p>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="clearfix"></div>
        <div class="text-center w-100">
            @if ( $confirmed )
                <button class="btn btn-success btn-width" disabled>No Show Confirmed</button>
            @else
                <button id="ns{{ $passenger->id }}" data-passenger="{{ $passenger->name }}" data-due="{{ $due }}" class="btn btn-info btn-width noshow">Confirm No Show</button>
            @endif
            <br><br>
        </div>
    </div>
@endif