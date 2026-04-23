@extends('layout')

@section('content')

<h3 class="mb-4">📄 AI Invoice Dashboard</h3>

<!-- ===================== -->
<!-- UPLOAD -->
<!-- ===================== -->
<div class="card mb-4">
    <div class="card-body">
        <form id="uploadForm">
            <div class="row g-2">
                <div class="col-md-9">
                    <input type="file" name="file" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100">
                        Upload Invoice
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ===================== -->
<!-- TABLE 1: RECENT -->
<!-- ===================== -->
<div class="card mb-4">
    <div class="card-header">
        🔄 Recent Uploads
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Status</th>
                    <th>Status Message</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody id="recentTable"></tbody>
        </table>
    </div>
</div>

<!-- ===================== -->
<!-- TABLE 2: ALL INVOICES -->
<!-- ===================== -->
<div class="card">
    <div class="card-header">
        📊 All Invoices
    </div>
    <div class="card-body">
        <table class="table" id="invoiceTable">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Filename</th>
                    <th>Processed At</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="allTable"></tbody>
        </table>
    </div>
</div>

<!-- MODAL DETAIL -->
<div class="modal fade" id="detailModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Invoice Detail</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detailContent"></div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>

// ===============================
// LOAD RECENT (LAST 5)
// ===============================
async function loadRecent() {
    const res = await fetch('/api/invoices');
    const json = await res.json();

    const data = json.data || [];

    let html = '';

    if (data.length === 0) {
        html = `
        <tr>
            <td colspan="3" class="text-center text-muted">
                No recent uploads
            </td>
        </tr>`;
    } else {
        data.slice(0, 1).forEach(inv => {
            const date = new Date(inv.created_at);

            const formattedDate = date.toLocaleString('id-ID', {
                weekday: 'long',   // Jumat
                day: 'numeric',    // 23
                month: 'long',     // April
                year: 'numeric',   // 2026
                hour: '2-digit',   // 04
                minute: '2-digit', // 38
                second: '2-digit'  // 45 (opsional)
            });

            html += `
            <tr>
                <td>${inv.id}</td>
                <td>${statusBadge(inv.status)}</td>
                <td>${inv.error_message ?? '-'}</td>
                <td>${formattedDate}</td>
            </tr>`;
        });
    }

    document.getElementById('recentTable').innerHTML = html;
}

// ===============================
// LOAD ALL INVOICES
// ===============================
let table;

async function loadAll() {
    const res = await fetch('/api/invoices');
    const json = await res.json();

    const data = json.data || [];

    let rows = [];

    data.forEach(inv => {

        let result = {};

        try {
            result = inv.result_json;
        } catch (e) {}

        let fileHtml = '-';

        if (inv.file_path) {
            fileHtml = `
                <a href="/storage/${inv.file_path}" target="_blank">
                    <img src="/storage/${inv.file_path}" width="50" />
                </a>
            `;
        }

        const date = new Date(inv.created_at);

        const formattedDate = date.toLocaleString('id-ID', {
            weekday: 'long',   // Jumat
            day: 'numeric',    // 23
            month: 'long',     // April
            year: 'numeric',   // 2026
            hour: '2-digit',   // 04
            minute: '2-digit', // 38
            second: '2-digit'  // 45 (opsional)
        });

        rows.push([
            inv.id,
            fileHtml,
            formattedDate ?? '-',
            statusBadge(inv.status),
            `<button class="btn btn-sm btn-info" onclick="showDetail(${inv.id})">Detail</button>`
        ]);
    });

    // destroy kalau sudah ada
    if ($.fn.DataTable.isDataTable('#invoiceTable')) {
        $('#invoiceTable').DataTable().destroy();
    }

    // kosongkan tbody
    $('#invoiceTable tbody').empty();

    // inject data
    rows.forEach(row => {
        $('#invoiceTable tbody').append(`
            <tr>
                <td>${row[0]}</td>
                <td>${row[1]}</td>
                <td>${row[2]}</td>
                <td>${row[3]}</td>
                <td>${row[4]}</td>
            </tr>
        `);
    });

    // init datatable
    table = $('#invoiceTable').DataTable({
        pageLength: 10, // 🔥 tampil 10 data
        order: [[0, 'desc']], // sort by id terbaru
    });
}

// ===============================
// STATUS BADGE
// ===============================
function statusBadge(status) {
    let color = 'secondary';

    if (status === 'done') color = 'success';
    else if (status === 'processing') color = 'warning';
    else if (status === 'failed') color = 'danger';

    return `<span class="badge bg-${color}">${status}</span>`;
}

// ===============================
// SHOW DETAIL (CALL API)
// ===============================
async function showDetail(id) {
    const res = await fetch(`/api/invoices/${id}`);
    const json = await res.json();

    const data = json.data;

    // FAILED
    if (data.status === 'failed') {
        document.getElementById('detailContent').innerHTML = `
            <div class="alert alert-danger">
                ${data.error_message ?? 'Processing failed'}
            </div>
        `;
        new bootstrap.Modal(document.getElementById('detailModal')).show();
        return;
    }

    // PROCESSING
    if (data.status !== 'done') {
        document.getElementById('detailContent').innerHTML = `
            <div class="alert alert-warning">
                ⏳ Processing...
            </div>
        `;
        new bootstrap.Modal(document.getElementById('detailModal')).show();
        return;
    }

    // PARSE JSON
    let result = {};
    try {
        result = data.result_json;
    } catch (e) {
        result = {};
    }

    // BASIC INFO
    let html = `
        <div class="mb-2"><strong>Invoice Number:</strong> ${result.invoice_number ?? '-'}</div>
        <div class="mb-2"><strong>Date:</strong> ${result.date ?? '-'}</div>
        <div class="mb-2"><strong>Vendor:</strong> ${result.vendor ?? '-'}</div>
        <div class="mb-2"><strong>Customer:</strong> ${result.customer ?? '-'}</div>
        <div class="mb-2"><strong>Total:</strong> ${formatCurrency(result.total)}</div>
    `;

    // ======================
    // ITEMS
    // ======================
    if (Array.isArray(result.items) && result.items.length > 0) {
        html += `<hr><h6>Items</h6>`;

        result.items.forEach(item => {
            html += `
                <div class="border p-2 mb-2 rounded">
                    <div><strong>Description:</strong> ${item.description ?? '-'}</div>
                    <div><strong>Price:</strong> ${formatCurrency(item.amount)}</div>
                </div>
            `;
        });
    } else {
        html += `<div class="text-muted mt-2">No items found</div>`;
    }

    document.getElementById('detailContent').innerHTML = html;

    new bootstrap.Modal(document.getElementById('detailModal')).show();
}

function formatCurrency(value) {
    if (!value) return '-';

    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(value);
}

// ===============================
// UPLOAD
// ===============================
document.getElementById('uploadForm').addEventListener('submit', async function(e){
    e.preventDefault();

    let formData = new FormData(this);

    await fetch('/api/invoices', {
        method: 'POST',
        body: formData
    });

    this.reset();

    loadRecent();
    loadAll();
});

// ===============================
// AUTO REFRESH
// ===============================
loadRecent();
loadAll();

setInterval(() => {
    loadRecent(); // hanya recent saja
}, 5000);

</script>

@endsection