<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Campaign Summary Card -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ number_format($this->record->total_recipients) }}</div>
                    <div class="text-sm text-gray-500">Total Recipients</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ number_format($this->record->sent_count) }}</div>
                    <div class="text-sm text-gray-500">Emails Sent</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ number_format($this->record->failed_count) }}</div>
                    <div class="text-sm text-gray-500">Failed</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $this->record->progress_percentage }}%</div>
                    <div class="text-sm text-gray-500">Progress</div>
                </div>
            </div>
            
            @if($this->record->total_recipients > 0)
                <div class="mt-4">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $this->record->progress_percentage }}%"></div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Recipients Table -->
        <div class="bg-white rounded-lg shadow">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>