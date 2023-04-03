@if ( is_null($passenger) )
    <div class="text-center mt-5"><h2>Passenger not found.</h2></div>
@else
    <div class="text-center w-100 mt-5">
        <h2>{{ $passenger->name }}</h2>
    </div>

    @if ( $confirmed )
        <div class="row mt-5">
            <div class="col-md-6 text-center">
                <button class="btn btn-success w-75 mb-3" disabled>Signature Saved</button>
            </div>
            <div class="col-md-6 text-center">
                <button class="btn btn-success w-75 mb-3" disabled>SMS Sent</button>
            </div>
        </div>
    @else
        <div id="todo_feedback" class="row mt-5">
            <div class="col-md-6 text-center">
                <button id="clearsig" class="btn btn-outline-secondary w-75 mb-3">Clear Signature</button>
                <button id="savesig" data-passenger="{{ $passenger->name }}" data-due="{{ $due }}" class="btn btn-info w-75 mb-3">Save Signature</button>
            </div>
            <div class="col-md-6 text-center">
                <p class="text-muted">If no signature just send sms.</p>
                <button id="sendsms" data-passenger="{{ $passenger->name }}" data-due="{{ $due }}" class="btn btn-info w-75">Send SMS</button>
            </div>

            <div class="col-12 mt-5">
                <div class="card text-white bg-dark mb-5">
                    <div class="card-header text-center">
                        <h5>Signature</h5>
                    </div>
                    <div class="d-flex card-body justify-content-center">
                        <div id="signature-pad" class="signature-pad">
                            <div class="signature-pad--body">
                                <canvas></canvas>
                            </div>
                        </div>
                        {!! Form::hidden('signature', null, ['id' => 'siginput']) !!}
                        {!! Form::hidden('trip', null, ['id' => 'tripid']) !!}
                        {!! Form::hidden('putime', null, ['id' => 'trippu']) !!}
                    </div>
                </div>
                <br><br>
            </div>
        </div>

        {{-- live confirmation ------------------------------------------------------------------------------------}}

        <div id="live_feedback" class="row mt-5 d-none">
            <div class="col-md-6 text-center">
                <button class="btn btn-success w-75 mb-3" disabled>Signature Saved</button>
            </div>
            <div class="col-md-6 text-center">
                <button class="btn btn-success w-75 mb-3" disabled>SMS Sent</button>
            </div>
        </div>
    @endif
@endif