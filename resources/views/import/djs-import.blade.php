<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DJ Import - CSV Upload</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .upload-area {
            border: 2px dashed #d1d5db;
            transition: all 0.3s ease;
        }
        .upload-area:hover {
            border-color: #3b82f6;
            background-color: #f8fafc;
        }
        .upload-area.dragover {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-8">
                <div class="text-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">DJ Import Tool</h1>
                    <p class="text-gray-600">Upload a CSV file to import DJs with auto-tagging</p>
                </div>

                <!-- Alert Messages -->
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                        <div class="font-medium">Success!</div>
                        <div class="whitespace-pre-line">{{ session('success') }}</div>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-6">
                        <div class="font-medium">Warning!</div>
                        <div class="whitespace-pre-line">{{ session('warning') }}</div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <div class="font-medium">Error!</div>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                        <div class="font-medium">Validation Errors:</div>
                        <ul class="list-disc list-inside mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Import Form -->
                <form action="{{ route('import.djs.process') }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            CSV File Upload
                        </label>
                        
                        <div class="upload-area rounded-lg p-6 text-center cursor-pointer" id="uploadArea">
                            <input type="file" name="csv_file" id="csvFile" accept=".csv,.txt" class="hidden" required>
                            
                            <div id="uploadContent">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="mt-4">
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium text-blue-600 hover:text-blue-500">Click to upload</span>
                                        or drag and drop your CSV file here
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">CSV files only, max 10MB</p>
                                </div>
                            </div>
                            
                            <div id="fileInfo" class="hidden">
                                <div class="flex items-center justify-center">
                                    <svg class="h-8 w-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="ml-2 text-sm font-medium text-gray-900" id="fileName"></span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Click to change file</p>
                            </div>
                        </div>
                    </div>

                    <!-- CSV Format Info -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <h3 class="font-medium text-blue-900 mb-2">CSV Format Requirements:</h3>
                        <ul class="text-sm text-blue-800 space-y-1">
                            <li>• Must contain <strong>email</strong> and <strong>name</strong> columns</li>
                            <li>• First row should be headers</li>
                            <li>• All users will be auto-tagged with <span class="bg-blue-200 px-2 py-1 rounded text-xs font-mono">import_2025</span></li>
                            <li>• Existing users will be updated, new users will be created</li>
                        </ul>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center">
                        <button type="submit" id="submitBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-8 rounded-lg transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="submitText">Import DJs</span>
                            <span id="submitSpinner" class="hidden">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const csvFile = document.getElementById('csvFile');
        const uploadContent = document.getElementById('uploadContent');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const importForm = document.getElementById('importForm');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitSpinner = document.getElementById('submitSpinner');

        // Upload area click handler
        uploadArea.addEventListener('click', () => csvFile.click());

        // File input change handler
        csvFile.addEventListener('change', handleFileSelect);

        // Drag and drop handlers
        uploadArea.addEventListener('dragover', handleDragOver);
        uploadArea.addEventListener('dragleave', handleDragLeave);
        uploadArea.addEventListener('drop', handleDrop);

        // Form submit handler
        importForm.addEventListener('submit', handleFormSubmit);

        function handleFileSelect(e) {
            const file = e.target.files[0];
            if (file) {
                displayFileInfo(file);
            }
        }

        function handleDragOver(e) {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        }

        function handleDragLeave(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        }

        function handleDrop(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                if (file.type === 'text/csv' || file.name.endsWith('.csv')) {
                    csvFile.files = files;
                    displayFileInfo(file);
                } else {
                    alert('Please upload a CSV file only.');
                }
            }
        }

        function displayFileInfo(file) {
            fileName.textContent = file.name;
            uploadContent.classList.add('hidden');
            fileInfo.classList.remove('hidden');
        }

        function handleFormSubmit(e) {
            submitBtn.disabled = true;
            submitText.classList.add('hidden');
            submitSpinner.classList.remove('hidden');
        }
    </script>
</body>
</html>