{{--<div class="mt-2">--}}
    {{--<h3 class="d-inline-block text-left">INVOICE</h3>--}}
    {{--<h5 class="d-inline-block text-right">{{ Carbon\Carbon::createFromFormat('Y-m-d',$month)->format('F Y') }}</h5>--}}
{{--</div>--}}
<div class="row">
    <div class="col">
        <h3 class="">INVOICE</h3>
    </div>
    <div class="col text-right">
        <h5 class="">{{ Carbon\Carbon::createFromFormat('Y-m-d',$month)->format('F Y') }}</h5>
    </div>
</div>

<div class="text-center">
    <small>{{ $customer->id }} :
    {{ $customer->billing_name }},
    {{ $customer->billing_adrs }}</small>
</div>

@if ( count($invoice) == 0 )
    <hr>
    <p>No shuttles in this period.</p>
@else

    <div class="table-responsive">
        <table class="table table-sm mt-3" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Shuttle</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>

            {{-- counters --}}
            @php $tot = $st_num = $st_val = 0; $date = $passenger = ''; @endphp

            @foreach ( $invoice as $entry )

                {{-- group by passenger --}}
                @if ( $entry->passenger != $passenger )

                    {{-- insert subtotals at end of passenger --}}
                    @if ( $st_num > 0 )
                        <tr>
                            <td></td>
                            <td class="text-right">{{ $st_num }} shuttles</td>
                            <td class="text-right">{{ number_format($st_val,2) }}</td>
                        </tr>
                        @php $tot += $st_val; $st_num = $st_val = 0; @endphp
                    @endif

                    {{-- insert passenger name as header --}}
                    <tr>
                        @if ( $entry->passenger == 'event' )
                            <td>Event Trips</td>
                        @else
                            <td colspan="3"><br>Shuttles: {{ $passengers[$entry->passenger] }}</td>
                        @endif
                    </tr>
                    @php $passenger = $entry->passenger; @endphp
                @endif

                <tr>
                    <td>{{ $date == $entry->date ? '' : $entry->date }}</td>
                    <td>{{ $entry->trip }}</td>
                    <td class="text-right">{{ number_format($entry->amount,2) }}</td>
                </tr>

                {{-- update counters --}}
                @php $st_num++; $st_val += $entry->amount; $date = $entry->date; @endphp

                {{-- insert final subtotal if it's not an event trip --}}
                @if ( $loop->last && $entry->passenger != 'event' )
                    <tr>
                        <td></td>
                        <td class="text-right">{{ $st_num }} shuttles</td>
                        <td class="text-right">{{ number_format($st_val,2) }}</td>
                    </tr>
                @endif

                {{-- add final entry to total --}}
                @if ( $loop->last )
                    @php $tot += $st_val; $st_num = $st_val = 0; @endphp
                @endif
            @endforeach

            {{-- insert invoice totals --}}
            @if ( $discount > 0 )
                <tr>
                    <td></td>
                    <td class="text-right"><br>Subtotal</td>
                    <td class="text-right"><br>{{ number_format($tot,2) }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td class="text-right">Discount</td>
                    <td class="text-right">{{ number_format($discount,2) }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td class="text-right"><strong>Total</strong></td>
                    <td class="text-right"><strong>{{ number_format(($tot - $discount),2) }}</strong></td>
                </tr>
            @else
                <tr>
                    <td></td>
                    <td class="text-right"><br><strong>Total</strong></td>
                    <td class="text-right"><br><strong>{{ number_format($tot,2) }}</strong></td>
                </tr>
            @endif
            </tbody>
        </table>
    </div>
@endif

<hr>
<div class="text-center">
    <small>Busy Bus (Pty) Ltd 2013/085512/07, Mainstream Centre, Hout Bay, www.shuttlebug.co.za</small><br><br>
</div>