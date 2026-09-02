@extends('layouts.app')

@section('content')

<div class="page-head">
    <div>
        <div class="eyebrow">CARD MANAGEMENT</div>

        <h1>My Virtual Card</h1>

        <p>
            Your virtual card will appear here after an Admin creates
            and assigns it to your account.
        </p>
    </div>
</div>

<div class="panel">

    <div class="empty-state">

        <div class="empty-state-icon">
            ▣
        </div>

        <h3>No Virtual Card Assigned</h3>

        <p>
            You don't have a virtual card yet.
            Please contact your Admin.
        </p>

    </div>

</div>

@endsection