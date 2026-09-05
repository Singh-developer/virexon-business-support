<!DOCTYPE html>
<html lang="en" class="h-full w-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Ticket - Agent Business Support</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-slate-50 text-slate-800 font-['Plus_Jakarta_Sans'] h-full flex flex-col">

    @include('partials.navbar')

    <main class="flex-grow max-w-3xl mx-auto w-full px-4 sm:px-6 py-8">
        
        <div class="mb-6">
            <a href="{{ route('tickets.index') }}" class="text-slate-500 hover:text-slate-800 text-sm font-medium mb-4 inline-block"><i class="fa-solid fa-arrow-left mr-1"></i> Back to Tickets</a>
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Create Support Ticket</h1>
            <p class="text-slate-500 mt-1">Describe your issue in detail and attach any relevant files.</p>
        </div>

        @if($errors->any())
            <div class="bg-red-50 text-red-700 border border-red-200 rounded-xl p-4 mb-6">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
            <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Subject / Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title') }}" required class="w-full rounded-xl border-slate-300 bg-slate-50 p-3 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none border transition" placeholder="Brief summary of your issue">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Message Description <span class="text-red-500">*</span></label>
                    <textarea name="message" required rows="6" class="w-full rounded-xl border-slate-300 bg-slate-50 p-3 focus:bg-white focus:ring-2 focus:ring-blue-500 outline-none border transition" placeholder="Please provide details about the issue...">{{ old('message') }}</textarea>
                </div>

                <div class="mb-8">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Attachment (Optional)</label>
                    <div class="border-2 border-dashed border-slate-300 rounded-xl p-6 text-center hover:bg-slate-50 transition cursor-pointer relative">
                        <input type="file" name="attachment" accept=".png,.jpeg,.jpg,.webp,.pdf" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="document.getElementById('file-name').innerText = this.files[0] ? this.files[0].name : 'No file chosen'">
                        <i class="fa-solid fa-cloud-arrow-up text-3xl text-slate-400 mb-2"></i>
                        <p class="text-sm text-slate-600 font-medium">Click to browse or drag and drop a file</p>
                        <p class="text-xs text-slate-500 mt-1">PNG, JPG, WEBP, PDF (Max 5MB)</p>
                        <p id="file-name" class="text-sm font-bold text-blue-600 mt-3"></p>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('tickets.index') }}" class="px-6 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-medium hover:bg-slate-50 transition">Cancel</a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-medium transition flex items-center gap-2">
                        <i class="fa-regular fa-paper-plane"></i> Submit Ticket
                    </button>
                </div>
            </form>
        </div>

    </main>

</body>
</html>

