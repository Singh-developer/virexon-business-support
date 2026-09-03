@extends('layouts.app')

@section('content')

<div class="page-head">

    <div>

        <div class="eyebrow">
            ACCOUNT SETTINGS
        </div>

        <h1>
            My Settings
        </h1>

        <p>
            Update your own account information.
        </p>

    </div>

</div>

<div class="panel form-panel">

    <form
        method="POST"
        action="{{ route('settings.profile.update') }}"
    >

        @csrf

        @method('PUT')

        <div class="form-grid">

            <label>

                Name

                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    required
                >

            </label>


            <label>

                Email

                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    required
                >

            </label>


            <label>

                Phone

                <input
                    type="text"
                    name="phone"
                    value="{{ old('phone', $user->phone) }}"
                >

            </label>


            <label>

                Role

                <input
                    type="text"
                    value="{{ $user->role?->name }}"
                    readonly
                >

            </label>

        </div>

        @if(!auth()->user()->isAgent())
        <div class="notice">
            You can edit your own profile information.
            Platform payment configuration is available only to Admin users.
        </div>
        @endif

        <div class="form-actions">

            <button class="btn primary">
                Save Changes
            </button>

        </div>

    </form>

</div>

@endsection