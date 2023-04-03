@if ( count($users) == 0 )
    <p class="alert alert-info">No match found</p>
@else
    <div class="list-group">
        @foreach ( $users as $user )
            <a class="list-group-item list-group-item-action flex-column align-items-start" href="{!! url('office/users/customers/edit/'.$user->id) !!}">
                <div class="d-flex w-100 justify-content-between">
                    <strong>{{ $user->name > ' ' ? $user->name : 'Unknown (profile incomplete)' }}</strong>
                    <span>{{ $user->status }}</span>
                </div>
                <div class="d-flex w-100 justify-content-between">
                    <span>{{ $user->address }}</span>
                    <span>{{ $user->id }}</span>
                </div>
                <small>Children:
                @foreach ( $user->children as $child )
                    @if ( $child->friend == '' )
                        {{ $child->name }} ({{ $child->id }}),
                    @endif
                @endforeach
                </small>
                <br>
                <small>Friends:
                    @foreach ( $user->children as $child )
                        @if ( $child->friend > '' )
                            {{ $child->name }},
                        @endif
                    @endforeach
                </small>
                <br>
                <small>{{ $user->email }}</small> {{ $user->phone }}
            </a>
        @endforeach
    </div>
@endif