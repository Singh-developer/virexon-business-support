<div class="panel">

    <div class="panel-head">

        <div>

            <h3>
                Payment Mode
            </h3>

            <p>
                Controls whether payment gateways operate in Sandbox or Live mode.
            </p>

        </div>

        <span class="badge {{ $paymentMode === 'live' ? 'success' : 'neutral' }}">

            {{ strtoupper($paymentMode) }}

        </span>

    </div>


    <form
        method="POST"
        action="{{ route('settings.payment-mode.update') }}"
    >

        @csrf

        <div class="form-grid">

            <label>

                Payment Mode

                <select name="payment_mode">

                    <option
                        value="sandbox"
                        @selected($paymentMode === 'sandbox')
                    >
                        Sandbox
                    </option>

                    <option
                        value="live"
                        @selected($paymentMode === 'live')
                    >
                        Live
                    </option>

                </select>

            </label>

        </div>


        <div class="notice">

            Only Admin and Super Admin users can change this setting.
            Agents do not have access to this configuration.

        </div>


        <div class="form-actions">

            <button class="btn primary">
                Save Payment Mode
            </button>

        </div>

    </form>

</div>