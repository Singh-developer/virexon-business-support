@extends('layouts.app')

@section('content')

    <div class="max-w-7xl mx-auto px-4 lg:px-6 py-8">

        @if(session('success'))
        <div class="mb-5 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center gap-3">
            <i class="fa-solid fa-circle-check text-green-500"></i> {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="mb-5 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center gap-3">
            <i class="fa-solid fa-circle-xmark text-red-500"></i> {{ session('error') }}
        </div>
        @endif

        <!-- Page Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900">Documents</h1>
            <p class="text-slate-500 text-sm mt-1">Upload and manage your documents securely</p>
        </div>

        <div class="flex flex-col xl:flex-row gap-6">

            <!-- Main Content -->
            <div class="flex-1 min-w-0">

                <!-- Info Banner -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg px-5 py-4 mb-6 flex items-start gap-3">
                    <i class="fa-solid fa-circle-info text-blue-500 mt-0.5"></i>
                    <div>
                        <div class="font-semibold text-blue-900 text-sm">Important</div>
                        <div class="text-blue-700 text-xs mt-0.5">Please upload clear and valid documents. All documents are encrypted and secure.</div>
                    </div>
                </div>

                <!-- Document Cards Grid -->
                @php
                    $docConfig = [
                        'pan_card'       => ['icon' => 'fa-id-card', 'color' => 'blue', 'label' => 'PAN Card', 'desc' => 'PAN Card, Address Proof, Photo', 'fields' => ['PAN Card', 'Address Proof', 'Photo'], 'accept' => '.jpg,.jpeg,.png,.pdf'],
                        'aadhaar'        => ['icon' => 'fa-address-card', 'color' => 'green', 'label' => 'Aadhaar Card', 'desc' => 'Front and back of Aadhaar', 'fields' => ['Front Side', 'Back Side'], 'accept' => '.jpg,.jpeg,.png,.pdf'],
                        'photo'          => ['icon' => 'fa-image', 'color' => 'purple', 'label' => 'Photograph', 'desc' => 'Recent passport size photograph', 'fields' => ['Passport Size Photo'], 'accept' => '.jpg,.jpeg,.png'],
                        'bank_proof'     => ['icon' => 'fa-building-columns', 'color' => 'yellow', 'label' => 'Bank Verification', 'desc' => 'Bank details & account proof', 'fields' => ['Cancelled Cheque', 'Account Details', 'IFSC Verification'], 'accept' => '.jpg,.jpeg,.png,.pdf'],
                        'agent_id_proof' => ['icon' => 'fa-id-badge', 'color' => 'orange', 'label' => 'Business Support Advance Agreement', 'desc' => 'Signed agreement document', 'fields' => ['Agreement Form', 'Firm Signature', 'Agent Signature'], 'accept' => '.jpg,.jpeg,.png,.pdf'],
                        'address_proof'  => ['icon' => 'fa-house-user', 'color' => 'red', 'label' => 'Address Proof', 'desc' => 'Voter ID / Utility Bill / Aadhaar', 'fields' => ['Address Proof Document'], 'accept' => '.jpg,.jpeg,.png,.pdf'],
                    ];

                    $colorMap = [
                        'blue'   => ['bg' => 'bg-blue-50',   'icon' => 'text-blue-500',   'border' => 'border-blue-100'],
                        'green'  => ['bg' => 'bg-green-50',  'icon' => 'text-green-500',  'border' => 'border-green-100'],
                        'purple' => ['bg' => 'bg-purple-50', 'icon' => 'text-purple-500', 'border' => 'border-purple-100'],
                        'yellow' => ['bg' => 'bg-yellow-50', 'icon' => 'text-yellow-500', 'border' => 'border-yellow-100'],
                        'orange' => ['bg' => 'bg-orange-50', 'icon' => 'text-orange-500', 'border' => 'border-orange-100'],
                        'red'    => ['bg' => 'bg-red-50',    'icon' => 'text-red-500',    'border' => 'border-red-100'],
                    ];
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
                    @foreach($docConfig as $type => $config)
                        @php
                            $doc    = $documents[$type] ?? null;
                            $status = $doc?->status ?? 'not_uploaded';
                            $colors = $colorMap[$config['color']];
                        @endphp
                        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5 flex flex-col justify-between min-h-[220px] {{ $status === 'rejected' || $status === 're_upload' ? 'border-red-200 ring-1 ring-red-200' : '' }}">
                            <div>
                                <!-- Header -->
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-lg {{ $colors['bg'] }} flex items-center justify-center">
                                            <i class="fa-regular {{ $config['icon'] }} {{ $colors['icon'] }} text-lg"></i>
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-900 text-sm leading-tight">{{ $config['label'] }}</div>
                                            <div class="text-slate-400 text-xs mt-0.5">{{ $config['desc'] }}</div>
                                        </div>
                                    </div>
                                    <!-- Status Badge -->
                                    @if($status === 'approved')
                                        <span class="text-xs font-bold text-green-700 bg-green-50 border border-green-200 px-2.5 py-1 rounded-full flex items-center gap-1"><i class="fa-solid fa-check text-[10px]"></i> Approved</span>
                                    @elseif($status === 'rejected')
                                        <span class="text-xs font-bold text-red-700 bg-red-50 border border-red-200 px-2.5 py-1 rounded-full flex items-center gap-1"><i class="fa-solid fa-xmark text-[10px]"></i> Rejected</span>
                                    @elseif($status === 're_upload')
                                        <span class="text-xs font-bold text-orange-700 bg-orange-50 border border-orange-200 px-2.5 py-1 rounded-full flex items-center gap-1"><i class="fa-solid fa-rotate-right text-[10px]"></i> Re-upload</span>
                                    @elseif($status === 'pending')
                                        <span class="text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 px-2.5 py-1 rounded-full flex items-center gap-1"><i class="fa-solid fa-clock text-[10px]"></i> Under Review</span>
                                    @else
                                        <span class="text-xs font-bold text-slate-500 bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-full">Not Uploaded</span>
                                    @endif
                                </div>

                                <!-- Field checklist -->
                                <ul class="space-y-1 mb-3">
                                    @foreach($config['fields'] as $field)
                                    <li class="flex items-center gap-2 text-xs text-slate-600">
                                        <i class="fa-solid fa-check {{ in_array($status, ['approved','pending','rejected','re_upload']) ? 'text-green-500' : 'text-slate-300' }} text-[10px]"></i>
                                        {{ $field }}
                                    </li>
                                    @endforeach
                                </ul>

                                <!-- Rejection note -->
                                @if($doc && $doc->admin_note && in_array($status, ['rejected', 're_upload']))
                                <div class="bg-red-50 border border-red-100 rounded-lg px-3 py-2 mb-3">
                                    <div class="text-xs font-semibold text-red-700 mb-0.5"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Admin Note:</div>
                                    <div class="text-xs text-red-600">{{ $doc->admin_note }}</div>
                                </div>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2 mt-auto pt-3 border-t border-slate-50">
                                @if($doc && $doc->file_path)
                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank"
                                   class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                                    <i class="fa-regular fa-eye"></i> View →
                                </a>
                                @endif

                                @if($status !== 'approved')
                                <button type="button"
                                    onclick="openUploadModal('{{ $type }}', '{{ $config['label'] }}', '{{ $config['accept'] }}')"
                                    class="ml-auto text-xs font-semibold px-3 py-1.5 rounded-lg {{ $status === 'rejected' || $status === 're_upload' ? 'bg-orange-500 hover:bg-orange-600 text-white' : 'bg-blue-600 hover:bg-blue-700 text-white' }} flex items-center gap-1.5 transition">
                                    <i class="fa-solid fa-arrow-up-from-bracket text-[10px]"></i>
                                    {{ in_array($status, ['rejected', 're_upload']) ? 'Re-upload' : 'Upload' }}
                                </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Document Status Summary -->
                @php
                    $totalDocs     = count($docConfig);
                    $uploadedDocs  = $documents->count();
                    $approvedDocs  = $documents->where('status', 'approved')->count();
                    $pendingDocs   = $documents->where('status', 'pending')->count();
                    $rejectedDocs  = $documents->whereIn('status', ['rejected', 're_upload'])->count();
                    $notUploaded   = $totalDocs - $uploadedDocs;
                @endphp

                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
                    <h3 class="font-bold text-slate-800 mb-4">Document Status Summary</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="bg-green-50 border border-green-100 rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-green-600">{{ $approvedDocs }}</div>
                            <div class="text-xs text-green-700 font-medium mt-1">Approved</div>
                        </div>
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ $pendingDocs }}</div>
                            <div class="text-xs text-blue-700 font-medium mt-1">Under Review</div>
                        </div>
                        <div class="bg-red-50 border border-red-100 rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-red-600">{{ $rejectedDocs }}</div>
                            <div class="text-xs text-red-700 font-medium mt-1">Rejected</div>
                        </div>
                        <div class="bg-slate-50 border border-slate-100 rounded-lg p-3 text-center">
                            <div class="text-2xl font-bold text-slate-500">{{ $notUploaded }}</div>
                            <div class="text-xs text-slate-600 font-medium mt-1">Pending Upload</div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-5">
                        <div class="flex justify-between text-xs font-medium text-slate-600 mb-1.5">
                            <span>Overall Progress</span>
                            <span>{{ $approvedDocs }}/{{ $totalDocs }} Approved</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full transition-all" style="width: {{ $totalDocs > 0 ? round($approvedDocs / $totalDocs * 100) : 0 }}%"></div>
                        </div>
                        @if($approvedDocs === $totalDocs)
                        <div class="mt-3 bg-green-50 border border-green-200 rounded-lg px-4 py-3 flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-green-500"></i>
                            <span class="text-green-800 font-semibold text-sm">You are Compliant! All required documents have been approved.</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="xl:w-72 space-y-5">

                <!-- Document Guidelines -->
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <i class="fa-solid fa-circle-info text-blue-500"></i>
                        <h3 class="font-bold text-slate-800">Document Guidelines</h3>
                    </div>
                    <ul class="space-y-2.5 text-xs text-slate-600">
                        <li class="flex items-start gap-2"><i class="fa-solid fa-circle-dot text-blue-400 mt-0.5 text-[8px]"></i> Upload clear and readable documents</li>
                        <li class="flex items-start gap-2"><i class="fa-solid fa-circle-dot text-blue-400 mt-0.5 text-[8px]"></i> Accepted formats: PDF, JPG, PNG</li>
                        <li class="flex items-start gap-2"><i class="fa-solid fa-circle-dot text-blue-400 mt-0.5 text-[8px]"></i> Maximum file size: 5MB per document</li>
                        <li class="flex items-start gap-2"><i class="fa-solid fa-circle-dot text-blue-400 mt-0.5 text-[8px]"></i> All documents are securely encrypted</li>
                        <li class="flex items-start gap-2"><i class="fa-solid fa-circle-dot text-blue-400 mt-0.5 text-[8px]"></i> Keep your documents up to date</li>
                    </ul>
                </div>

                <!-- Need Help -->
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <i class="fa-regular fa-circle-question text-slate-500"></i>
                        <h3 class="font-bold text-slate-800">Need Help?</h3>
                    </div>
                    <p class="text-xs text-slate-500 mb-4">If you face any issue while uploading or verifying documents, our support team is here to help you.</p>
                    <a href="{{ route('tickets.create') }}" class="flex items-center justify-center gap-2 w-full bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold py-2.5 px-4 rounded-lg transition">
                        <i class="fa-regular fa-headset"></i> Contact Support
                    </a>
                    <div class="mt-3 pt-3 border-t border-slate-100 text-center text-xs text-slate-500">
                        <!-- <div class="font-semibold text-slate-700">0120-1234567</div> -->
                        <div>support@virexon.in</div>
                    </div>
                </div>

                <!-- Application Status -->
                <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <i class="fa-solid fa-layer-group text-slate-500"></i>
                        <h3 class="font-bold text-slate-800">Application Flow</h3>
                    </div>
                    @php $appStatus = auth()->user()->detail?->status ?? 'pending'; @endphp
                    <ol class="space-y-3">
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full text-xs font-bold flex items-center justify-center shrink-0 {{ in_array($appStatus, ['form_received','approved','pending']) ? 'bg-green-500 text-white' : 'bg-slate-100 text-slate-500' }}"><i class="fa-solid fa-check text-[10px]"></i></span>
                            <span class="text-xs {{ in_array($appStatus, ['form_received','approved','pending']) ? 'text-slate-800 font-semibold' : 'text-slate-400' }}">Fund Application Submitted</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full text-xs font-bold flex items-center justify-center shrink-0 {{ $appStatus === 'form_received' || $appStatus === 'approved' ? 'bg-green-500 text-white' : 'bg-slate-100 text-slate-500' }}">{{ $appStatus === 'form_received' || $appStatus === 'approved' ? '<i class="fa-solid fa-check text-[10px]"></i>' : '2' }}</span>
                            <span class="text-xs {{ $appStatus === 'form_received' || $appStatus === 'approved' ? 'text-slate-800 font-semibold' : 'text-slate-400' }}">Form Received by Admin</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full text-xs font-bold flex items-center justify-center shrink-0 bg-blue-500 text-white">3</span>
                            <span class="text-xs text-slate-800 font-semibold">Upload Documents</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full text-xs font-bold flex items-center justify-center shrink-0 {{ $appStatus === 'approved' ? 'bg-green-500 text-white' : 'bg-slate-100 text-slate-500' }}">4</span>
                            <span class="text-xs {{ $appStatus === 'approved' ? 'text-slate-800 font-semibold' : 'text-slate-400' }}">Final Approval</span>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div id="upload-modal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-900" id="modal-title">Upload Document</h3>
                <button onclick="closeUploadModal()" class="text-slate-400 hover:text-slate-700"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>

            <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" id="upload-form">
                @csrf
                <input type="hidden" name="document_type" id="modal-doc-type">

                <!-- Drop Zone -->
                <div id="drop-zone" class="border-2 border-dashed border-blue-200 rounded-xl p-8 text-center cursor-pointer hover:bg-blue-50 transition mb-4 group"
                     onclick="document.getElementById('file-input').click()">
                    <i class="fa-solid fa-cloud-arrow-up text-3xl text-blue-400 mb-2 group-hover:text-blue-600"></i>
                    <p class="text-sm font-semibold text-slate-700">Click or drag a file here</p>
                    <p class="text-xs text-slate-400 mt-1" id="modal-accept-text">PDF, JPG, PNG up to 5MB</p>
                    <input type="file" name="file" id="file-input" class="hidden" required onchange="updateFileName(this)">
                </div>

                <div id="file-name-display" class="hidden mb-3 bg-green-50 border border-green-200 rounded-lg px-3 py-2 flex items-center gap-2 text-sm text-green-700">
                    <i class="fa-solid fa-file-check"></i>
                    <span id="file-name-text"></span>
                </div>

                <p class="text-xs text-slate-400 mb-5">
                    <i class="fa-solid fa-shield-halved mr-1 text-green-500"></i>
                    Your file will be encrypted and stored securely.
                </p>

                <div class="flex gap-3">
                    <button type="button" onclick="closeUploadModal()"
                        class="flex-1 py-2.5 border border-slate-200 text-slate-600 text-sm font-medium rounded-lg hover:bg-slate-50 transition">
                        Cancel
                    </button>
                    <button type="submit" id="upload-btn"
                        class="flex-1 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-arrow-up-from-bracket"></i> Upload
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openUploadModal(type, label, accept) {
            document.getElementById('modal-doc-type').value = type;
            document.getElementById('modal-title').textContent = 'Upload: ' + label;
            document.getElementById('file-input').accept = accept;
            document.getElementById('file-name-display').classList.add('hidden');
            document.getElementById('upload-modal').classList.remove('hidden');
        }

        function closeUploadModal() {
            document.getElementById('upload-modal').classList.add('hidden');
            document.getElementById('file-input').value = '';
        }

        function updateFileName(input) {
            if (input.files.length > 0) {
                document.getElementById('file-name-text').textContent = input.files[0].name;
                document.getElementById('file-name-display').classList.remove('hidden');
            }
        }

        // Drag and drop
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('file-input');

        dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('border-blue-500', 'bg-blue-50'); });
        dropZone.addEventListener('dragleave', () => { dropZone.classList.remove('border-blue-500', 'bg-blue-50'); });
        dropZone.addEventListener('drop', e => {
            e.preventDefault();
            dropZone.classList.remove('border-blue-500', 'bg-blue-50');
            const files = e.dataTransfer.files;
            if (files.length) {
                fileInput.files = files;
                updateFileName(fileInput);
            }
        });

        // Submit with loading state
        document.getElementById('upload-form').addEventListener('submit', function() {
            const btn = document.getElementById('upload-btn');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Uploading...';
            btn.disabled = true;
        });

        // Close modal on backdrop click
        document.getElementById('upload-modal').addEventListener('click', function(e) {
            if (e.target === this) closeUploadModal();
        });
    </script>
@endsection
