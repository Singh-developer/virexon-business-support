@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

<style>
    .dt-buttons { margin-bottom: 15px; }
    .dataTables_wrapper .dataTables_filter { margin-bottom: 15px; }
    .dataTables_wrapper .dataTables_length { margin-bottom: 15px; }
    .table-wrap { overflow-x: auto; background: white; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0; }
    .filter-container { display: flex; gap: 15px; margin-bottom: 20px; align-items: center; background: #f8fafc; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; flex-wrap: wrap; }
    .filter-container > div { display: flex; flex-direction: column; gap: 5px; }
    .filter-container input, .filter-container select { padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 13px; color: #1e293b; background: white; }
    .filter-container label { font-size: 12px; font-weight: 600; color: #475569; text-transform: uppercase; }
    .page-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }

    table.dataTable thead th { border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 12px; text-transform: uppercase; background: #f8fafc; white-space: nowrap; }
    table.dataTable tbody td { border-bottom: 1px solid #f1f5f9; color: #1e293b; font-size: 13px; vertical-align: middle; }
    table.dataTable tbody tr:hover { background-color: #f8fafc; }
</style>

@php $isAdmin = auth()->user()->isAdmin(); @endphp

<div class="page-head">
    <div>
        <div class="eyebrow">PAYMENT MANAGEMENT</div>
        <h1>Payments</h1>
        <p style="color:#64748b; font-size:14px; margin-top:5px;">Gateway-aware payment operations with status, verification and auditability. Filter and search live.</p>
    </div>
    <a class="btn primary" href="{{ route('payments.create') }}">+ New Payment</a>
</div>

<div class="panel" style="padding: 0; background: transparent; border: none; box-shadow: none;">
    <div class="filter-container">
        <div>
            <label>Start Date</label>
            <input type="date" id="pay-min-date" name="min">
        </div>
        <div>
            <label>End Date</label>
            <input type="date" id="pay-max-date" name="max">
        </div>
        @if($isAdmin)
        <div>
            <label>Agent</label>
            <select id="pay-agent-filter">
                <option value="">All agents</option>
                @foreach($agents as $agent)
                    <option value="{{ $agent->id }}" @selected((string) request('agent_id') === (string) $agent->id)>{{ $agent->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div>
            <label>Status</label>
            <select id="pay-status-filter">
                <option value="">All statuses</option>
                @foreach(['created','pending','processing','successful','failed','cancelled','refunded'] as $s)
                    <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Gateway</label>
            <select id="pay-gateway-filter">
                <option value="">All gateways</option>
                <option value="mock" @selected(request('gateway') === 'mock')>Mock</option>
                <option value="razorpay" @selected(request('gateway') === 'razorpay')>Razorpay</option>
                <option value="paytm" @selected(request('gateway') === 'paytm')>Paytm</option>
            </select>
        </div>
        <div>
            <label>Type</label>
            <select id="pay-type-filter">
                <option value="">All types</option>
                <option value="spending" @selected(request('payment_type') === 'spending')>Spending</option>
                <option value="repayment" @selected(request('payment_type') === 'repayment')">Repayment</option>
            </select>
        </div>
        <div style="flex-grow: 1;"></div>
    </div>

    <div class="table-wrap">
        <table id="paymentsTable" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>Payment</th>
                    @if($isAdmin)
                        <th>Agent</th>
                    @endif
                    <th>Business</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Gateway</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $p)
                    <tr data-created="{{ $p->created_at?->format('Y-m-d') }}"
                        data-agent="{{ $p->user_id }}"
                        data-status="{{ $p->status->value }}"
                        data-gateway="{{ strtolower($p->gateway) }}"
                        data-type="{{ $p->isRepayment() ? 'repayment' : 'spending' }}">
                        <td><a class="link mono" href="{{ route('payments.show', $p) }}">{{ $p->reference }}</a></td>
                        @if($isAdmin)
                            <td>{{ $p->user?->name ?? '—' }}</td>
                        @endif
                        <td>{{ $p->business->name }}</td>
                        <td>
                            @if($p->isRepayment())
                                <span class="badge" style="background: #E8F8F5; color: #27AE60;">Repayment</span>
                            @else
                                <span class="badge" style="background: #FEF5E7; color: #E65100;">Spending</span>
                            @endif
                        </td>
                        <td data-order="{{ $p->isRepayment() ? -$p->amount : $p->amount }}" style="font-weight: 600; color: {{ $p->isRepayment() ? '#27AE60' : '#1565C0' }}">
                            @if($p->isRepayment())−@endif ₹{{ number_format($p->amount, 2) }}
                        </td>
                        <td><span class="badge neutral">{{ strtoupper($p->gateway) }}</span></td>
                        <td><span class="badge {{ $p->status->value }}">{{ ucfirst($p->status->value) }}</span></td>
                        <td data-order="{{ $p->created_at?->timestamp ?? 0 }}">{{ $p->created_at->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 8 : 7 }}" style="text-align:center; padding: 30px; color:#64748b;">No payments yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>

<script>
$(document).ready(function() {
    var table = $('#paymentsTable').DataTable({
        dom: 'Blfrtip',
        buttons: [
            { extend: 'colvis', text: 'Select Columns', className: 'btn secondary tiny' },
            { extend: 'copy', className: 'btn secondary tiny', exportOptions: { columns: ':visible' } },
            { extend: 'csv', className: 'btn secondary tiny', exportOptions: { columns: ':visible' } },
            { extend: 'excel', className: 'btn secondary tiny', exportOptions: { columns: ':visible' } },
            { extend: 'pdf', className: 'btn secondary tiny', exportOptions: { columns: ':visible' }, orientation: 'landscape' },
            { extend: 'print', className: 'btn secondary tiny', exportOptions: { columns: ':visible' } }
        ],
        pageLength: 20,
        lengthMenu: [20, 40, 100],
        order: [[{{ $isAdmin ? 7 : 6 }}, 'desc']],
        language: {
            search: "Quick Search:"
        },
        // Seed live search from ?q= so old shared links keep working.
        search: { search: {!! json_encode(request('q', '')) !!} }
    });

    $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
        if (settings.nTable.id !== 'paymentsTable') return true;
        var node = table.row(dataIndex).node();
        if (!node) return true;

        var min = $('#pay-min-date').val();
        var max = $('#pay-max-date').val();
        var dateStr = $(node).data('created');
        if (min && dateStr < min) return false;
        if (max && dateStr > max) return false;

        var agentVal = $('#pay-agent-filter').val();
        if (agentVal && String($(node).data('agent')) !== agentVal) return false;

        var statusVal = $('#pay-status-filter').val();
        if (statusVal && $(node).data('status') !== statusVal) return false;

        var gatewayVal = $('#pay-gateway-filter').val();
        if (gatewayVal && String($(node).data('gateway')).toLowerCase() !== String(gatewayVal).toLowerCase()) return false;

        var typeVal = $('#pay-type-filter').val();
        if (typeVal && $(node).data('type') !== typeVal) return false;

        return true;
    });

    $('#pay-min-date, #pay-max-date, #pay-agent-filter, #pay-status-filter, #pay-gateway-filter, #pay-type-filter').on('change', function() {
        table.draw();
    });
});
</script>
@endsection
