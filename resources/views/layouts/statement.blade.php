<div class="row">
    <div class="col">
        <h3 class="">STATEMENT</h3>
    </div>
    <div class="col text-right">
        <h5 class="">{{ now()->format('d F Y') }}</h5>
    </div>
</div>

<div class="text-center small">
    {{ $customer->id }} :
    {{ $customer->billing_name }},
    {{ $customer->billing_adrs }}
</div>

@if ( count($statement) == 0 && count($journals) == 0 )
    <hr>
    <p>No transactions to date.</p>
@else

    <div class="table-responsive">
        <table class="table table-striped table-sm mt-3" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Transaction</th>
                    <th class="text-right">Amount</th>
                    <th class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
            @foreach ( $journals as $entry )
                <tr>
                    <td>{{ $entry->date }}</td>
                    <td>{{ $entry->entry }}</td>
                    <td class="text-right">{{ number_format($entry->amount,2) }}</td>
                    <td class="text-right">{{ number_format($entry->balance,2) }}</td>
                </tr>
            @endforeach

            @foreach ( $statement as $entry )
                <tr>
                    <td>{{ $entry->date }}</td>
                    <td>{{ $entry->transaction }}</td>
                    <td class="text-right">{{ number_format($entry->amount,2) }}</td>
                    <td class="text-right">{{ number_format($entry->balance,2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif

<hr>
<div class="text-center small">
    Busy Bus (Pty) Ltd 2013/085512/07, Mainstream Centre, Hout Bay, www.shuttlebug.co.za<br><br>
</div>
