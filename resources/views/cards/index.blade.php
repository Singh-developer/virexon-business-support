@extends('layouts.app')

@section('content')

<div class="page-head">

    <div>

        <div class="eyebrow">
            CARD MANAGEMENT
        </div>

        <h1>
            Virtual Cards
        </h1>

        <p>
            Sandbox cards use encrypted PAN storage and provider
            references. CVV is never retained permanently.
        </p>

    </div>


    @if(auth()->user()->isAdmin())

        <a
            class="btn primary"
            href="{{ route('cards.create') }}"
        >
            + Create Card
        </a>

    @endif

</div>


<div class="panel">


    <form class="toolbar">

        <input
            name="q"
            value="{{ request('q') }}"
            placeholder="Search card, holder or last 4 digits"
        >

        <button class="btn secondary">
            Search
        </button>

    </form>


    <div class="table-wrap">

        <table>

            <thead>

                <tr>

                    <th>Card</th>

                    <th>Business</th>

                    <th>Agent</th>

                    <th>Limits</th>

                    <th>Usage</th>

                    <th>Expiry</th>

                    <th>Status</th>

                </tr>

            </thead>


            <tbody>

                @forelse($cards as $card)

                    <tr>

                        <td>

                            <a
                                class="link mono"
                                href="{{ route('cards.show', $card) }}"
                            >

                                {{ $card->reference }}

                            </a>

                            <div class="muted mono">

                                •••• {{ $card->last4 }}

                            </div>

                        </td>


                        <td>

                            {{ $card->business->name }}

                        </td>


                        <td>

                            {{ $card->agent?->name ?? '—' }}

                        </td>


                        <td>

                            ₹{{ number_format($card->card_limit, 2) }}

                            <div class="muted">

                                Daily
                                ₹{{ number_format($card->daily_limit, 2) }}

                                ·

                                Txn
                                ₹{{ number_format($card->per_transaction_limit, 2) }}

                            </div>

                        </td>


                        <td>

                            <strong>
                                ₹{{ number_format($card->current_usage, 2) }}
                            </strong>

                            <div class="muted">

                                ₹{{ number_format($card->remaining_limit, 2) }}
                                remaining

                            </div>

                        </td>


                        <td>

                            {{ $card->expiry_date->format('d M Y') }}

                        </td>


                        <td>

                            <span class="badge success">

                                {{ ucfirst($card->status->value) }}

                            </span>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="7"
                            style="text-align:center;"
                        >

                            No virtual cards found.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    <div class="pagination">

        {{ $cards->links() }}

    </div>

</div>

@endsection