<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>TodoApp - Kelola Tugas Anda</title>
    @vite('resources/css/app.css')
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen" x-data="todoApp()">
    @include('components.navbar')
    
    <!-- Alert Messages -->
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition class="max-w-6xl mx-auto px-3 mt-3">
            <div class="bg-green-200 border border-green-300 text-green-800 px-3 py-2 rounded">
                <span>{{ session('success') }}</span>
                <span class="float-right cursor-pointer" @click="show = false">×</span>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition class="max-w-6xl mx-auto px-3 mt-3">
            <div class="bg-red-200 border border-red-300 text-red-800 px-3 py-2 rounded">
                <span>{{ session('error') }}</span>
                <span class="float-right cursor-pointer" @click="show = false">×</span>
            </div>
        </div>
    @endif

    <div class="max-w-6xl mx-auto px-3 py-6">
        <!-- Header Section -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-1">Daftar Tugas</h1>
            <p class="text-gray-600 text-sm">Kelola tugas harian Anda</p>
        </div>

        <!-- Add Todo Button -->
        <div class="mb-4">
            <button @click="openAddModal()" 
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm">
                + Tambah Tugas
            </button>
        </div>

        <!-- Todo List -->
        <div class="space-y-3 mb-6">
            @forelse($todos as $todo)
                @php
                    $isCompleted = $todo->status === 'selesai';
                    $isPending = $todo->status === 'belum_dikerjakan' || $todo->status === 'proses';
                    $statusColors = [
                        'belum_dikerjakan' => 'bg-red-100 text-red-600 border border-red-200',
                        'proses' => 'bg-yellow-100 text-yellow-700 border border-yellow-200',
                        'selesai' => 'bg-green-100 text-green-700 border border-green-200'
                    ];
                    $statusText = [
                        'belum_dikerjakan' => 'Belum Dikerjakan',
                        'proses' => 'Proses',
                        'selesai' => 'Selesai'
                    ];
                @endphp
                <div class="bg-white border border-gray-300 rounded p-4 shadow-sm">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            @php
                                $statusColors = [
                                    'belum_dikerjakan' => 'bg-red-200 text-red-800',
                                    'proses' => 'bg-yellow-200 text-yellow-800',
                                    'selesai' => 'bg-green-200 text-green-800'
                                ];
                                $statusText = [
                                    'belum_dikerjakan' => 'Belum Dikerjakan',
                                    'proses' => 'Proses',
                                    'selesai' => 'Selesai'
                                ];
                            @endphp
                            <span class="inline-block px-2 py-1 rounded text-xs font-semibold {{ $statusColors[$todo->status] ?? 'bg-gray-200 text-gray-800' }}">
                                {{ $statusText[$todo->status] ?? $todo->status }}
                            </span>
                            <h3 class="text-lg font-medium text-gray-900 mt-2 cursor-pointer @if($isCompleted) line-through text-gray-400 @endif" @click="viewTodo({{ $todo->id }})">
                                {{ $todo->judul_tugas }}
                            </h3>
                            <p class="text-gray-600 text-sm mt-1 @if($isCompleted) line-through text-gray-400 @endif">{{ Str::limit($todo->deskripsi_tugas, 80) }}</p>
                            @if($todo->categories && $todo->categories->count() > 0)
                                <div class="mt-2">
                                    @foreach($todo->categories as $category)
                                        <span class="inline-block px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded mr-1">
                                            {{ $category->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                            <div class="text-xs text-gray-500 mt-2">
                                Deadline: {{ \Carbon\Carbon::parse($todo->tanggal_selesai)->format('d M Y') }}
                            </div>
                            @if($isCompleted)
                                <div class="text-xs text-green-700 mt-1">
                                    Selesai pada: {{ \Carbon\Carbon::parse($todo->updated_at)->format('d M Y') }}
                                </div>
                            @endif
                        </div>
                        <div class="flex space-x-2 ml-4">
                            <button @click="viewTodo({{ $todo->id }})" class="text-blue-600 hover:text-blue-800 p-1" title="Lihat">
                                <i class="fas fa-eye"></i>
                            </button>
                            @if(!$isCompleted)
                            <button @click="editTodo({{ $todo->id }})" class="text-green-600 hover:text-green-800 p-1" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            @endif
                            <button @click="deleteTodo({{ $todo->id }})" class="text-red-600 hover:text-red-800 p-1" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                            @if($isPending)
                            <form method="POST" action="/todo/complete/{{ $todo->id }}">
                                @csrf
                                <button type="submit" class="text-gray-700 hover:text-green-700 p-1" title="Selesaikan">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <i class="fas fa-tasks text-gray-400 text-4xl mb-3"></i>
                    <h3 class="text-lg text-gray-600 mb-2">Belum ada tugas</h3>
                    <p class="text-gray-500 text-sm mb-3">Mulai dengan menambahkan tugas pertama!</p>
                    <button @click="openAddModal()" 
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded text-sm">
                        Tambah Tugas
                    </button>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($todos->hasPages())
            <div class="flex justify-center">
                {{ $todos->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Tambah Tugas -->
    <div x-show="showAddModal" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto border border-gray-200">
            <div class="p-0">
                <!-- Header gradasi dan ikon -->
                <div class="rounded-t-2xl bg-gradient-to-r from-green-200 via-blue-100 to-purple-100 px-6 pt-6 pb-4 flex flex-col items-center relative">
                    <div class="bg-green-100 text-green-600 rounded-full p-4 mb-2 shadow">
                        <i class="fas fa-plus fa-2x"></i>
                    </div>
                    <span class="absolute top-3 right-4 cursor-pointer text-gray-400 hover:text-gray-700 text-2xl" @click="closeAddModal()">&times;</span>
                    <h3 class="text-2xl font-extrabold text-gray-800 mt-2 mb-1 text-center">Tambah Tugas</h3>
                </div>
                <form method="POST" action="{{ route('todo.add') }}" class="px-6 pb-6 pt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold mb-1 text-gray-700">Judul Tugas *</label>
                        <input type="text" name="judul_tugas" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1 text-gray-700">Deskripsi *</label>
                        <textarea name="deskripsi_tugas" rows="3" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold mb-1 text-gray-700">Deadline *</label>
                            <input type="date" name="tanggal_selesai" required min="{{ date('Y-m-d') }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1 text-gray-700">Status *</label>
                            <select name="status" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition">
                                <option value="belum_dikerjakan">Belum Dikerjakan</option>
                                <option value="proses">Proses</option>
                                <option value="selesai">Selesai</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1 text-gray-700">Kategori</label>
                        <div class="space-y-1 max-h-24 overflow-y-auto">
                            @foreach($categories as $category)
                                <label class="flex items-center text-sm">
                                    <input type="checkbox" name="categories[]" value="{{ $category->id }}" class="rounded border-gray-300 mr-2">
                                    {{ $category->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-4 border-t border-gray-100 mt-2">
                        <button type="button" @click="closeAddModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 flex items-center gap-2">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Tugas -->
    <div x-show="showEditModal" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto border border-gray-200">
            <div class="p-0">
                <!-- Header gradasi dan ikon -->
                <div class="rounded-t-2xl bg-gradient-to-r from-yellow-200 via-blue-100 to-purple-100 px-6 pt-6 pb-4 flex flex-col items-center relative">
                    <div class="bg-yellow-100 text-yellow-600 rounded-full p-4 mb-2 shadow">
                        <i class="fas fa-edit fa-2x"></i>
                    </div>
                    <span class="absolute top-3 right-4 cursor-pointer text-gray-400 hover:text-gray-700 text-2xl" @click="closeEditModal()">&times;</span>
                    <h3 class="text-2xl font-extrabold text-gray-800 mt-2 mb-1 text-center">Edit Tugas</h3>
                </div>
                <form :action="`/todo/update/${selectedTodo.id}`" method="POST" class="px-6 pb-6 pt-4 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-semibold mb-1 text-gray-700">Judul Tugas *</label>
                        <input type="text" name="judul_tugas" :value="selectedTodo.judul_tugas" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1 text-gray-700">Deskripsi *</label>
                        <textarea name="deskripsi_tugas" rows="3" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition" x-text="selectedTodo.deskripsi_tugas"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold mb-1 text-gray-700">Deadline *</label>
                            <input type="date" name="tanggal_selesai" :value="selectedTodo.tanggal_selesai" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold mb-1 text-gray-700">Status *</label>
                            <select name="status" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition">
                                <option value="belum_dikerjakan" :selected="selectedTodo.status === 'belum_dikerjakan'">Belum Dikerjakan</option>
                                <option value="proses" :selected="selectedTodo.status === 'proses'">Proses</option>
                                <option value="selesai" :selected="selectedTodo.status === 'selesai'">Selesai</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1 text-gray-700">Kategori</label>
                        <div class="space-y-1 max-h-24 overflow-y-auto">
                            @foreach($categories as $category)
                                <label class="flex items-center text-sm">
                                    <input type="checkbox" name="categories[]" value="{{ $category->id }}"
                                        x-bind:checked="selectedTodo.categories && selectedTodo.categories.some(function(cat){ return cat.id === {{ $category->id }} })"
                                        class="rounded border-gray-300 mr-2">
                                    {{ $category->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-4 border-t border-gray-100 mt-2">
                        <button type="button" @click="closeEditModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 flex items-center gap-2">
                            <i class="fas fa-times"></i> Batal
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
                            <i class="fas fa-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal View Tugas -->
    <div x-show="showViewModal" x-transition.opacity class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto border border-gray-200">
            <div class="p-0">
                <!-- Header gradasi dan ikon status -->
                <div class="rounded-t-2xl bg-gradient-to-r from-blue-200 via-purple-100 to-pink-100 px-6 pt-6 pb-4 flex flex-col items-center relative">
                    <template x-if="selectedTodo.status === 'belum_dikerjakan'">
                        <div class="bg-red-100 text-red-600 rounded-full p-4 mb-2 shadow">
                            <i class="fas fa-hourglass-start fa-2x"></i>
                        </div>
                    </template>
                    <template x-if="selectedTodo.status === 'proses'">
                        <div class="bg-yellow-100 text-yellow-600 rounded-full p-4 mb-2 shadow">
                            <i class="fas fa-spinner fa-2x animate-spin"></i>
                        </div>
                    </template>
                    <template x-if="selectedTodo.status === 'selesai'">
                        <div class="bg-green-100 text-green-600 rounded-full p-4 mb-2 shadow">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </template>
                    <span class="absolute top-3 right-4 cursor-pointer text-gray-400 hover:text-gray-700 text-2xl" @click="closeViewModal()">&times;</span>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold tracking-wide mt-2 mb-1"
                        :class="{
                            'bg-red-200 text-red-800': selectedTodo.status === 'belum_dikerjakan',
                            'bg-yellow-200 text-yellow-800': selectedTodo.status === 'proses',
                            'bg-green-200 text-green-800': selectedTodo.status === 'selesai'
                        }"
                        x-text="getStatusText(selectedTodo.status)">
                    </span>
                    <h4 class="text-2xl font-extrabold text-gray-800 mt-2 mb-1 text-center" x-text="selectedTodo.judul_tugas"></h4>
                </div>
                <div class="px-6 pb-6 pt-4 space-y-4">
                    <div>
                        <h5 class="text-sm font-semibold mb-1 text-gray-700">Deskripsi:</h5>
                        <p class="text-gray-600 text-base" x-text="selectedTodo.deskripsi_tugas"></p>
                    </div>
                    <div x-show="selectedTodo.categories && selectedTodo.categories.length > 0">
                        <h5 class="text-sm font-semibold mb-1 text-gray-700">Kategori:</h5>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="category in selectedTodo.categories" :key="category.id">
                                <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-xs rounded-full font-semibold shadow-sm" x-text="category.name"></span>
                            </template>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 gap-2 mt-2">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar-plus text-blue-400"></i>
                            <span class="text-xs text-gray-500">Dibuat:</span>
                            <span class="text-gray-700 text-sm font-medium" x-text="formatDate(selectedTodo.created_at)"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-calendar-day text-pink-400"></i>
                            <span class="text-xs text-gray-500">Deadline:</span>
                            <span class="text-gray-700 text-sm font-medium" x-text="formatDate(selectedTodo.tanggal_selesai)"></span>
                        </div>
                        <div class="flex items-center gap-2" x-show="selectedTodo.status === 'selesai'">
                            <i class="fas fa-calendar-check text-green-500"></i>
                            <span class="text-xs text-gray-500">Tanggal Selesai:</span>
                            <span class="text-green-700 text-sm font-bold" x-text="formatDate(selectedTodo.tanggal_selesai)"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function todoApp() {
            return {
                showAddModal: false,
                showEditModal: false,
                showViewModal: false,
                selectedTodo: {},
                todos: @json($todos->items()),

                openAddModal() {
                    this.showAddModal = true;
                    document.body.style.overflow = 'hidden';
                },

                closeAddModal() {
                    this.showAddModal = false;
                    document.body.style.overflow = 'auto';
                    const form = document.querySelector('form[action="{{ route('todo.add') }}"]');
                    if (form) form.reset();
                },

                editTodo(todoId) {
                    const todo = this.todos.find(t => t.id === todoId);
                    if (todo) {
                        this.selectedTodo = { ...todo };
                        this.showEditModal = true;
                        this.showViewModal = false;
                        document.body.style.overflow = 'hidden';
                    }
                },

                closeEditModal() {
                    this.showEditModal = false;
                    this.selectedTodo = {};
                    document.body.style.overflow = 'auto';
                },

                viewTodo(todoId) {
                    const todo = this.todos.find(t => t.id === todoId);
                    if (todo) {
                        this.selectedTodo = { ...todo };
                        this.showViewModal = true;
                        document.body.style.overflow = 'hidden';
                    }
                },

                closeViewModal() {
                    this.showViewModal = false;
                    this.selectedTodo = {};
                    document.body.style.overflow = 'auto';
                },

                deleteTodo(todoId) {
                    if (confirm('Yakin ingin menghapus tugas ini?')) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `/todo/delete/${todoId}`;
                        form.innerHTML = `
                            @csrf
                            @method('DELETE')
                        `;
                        document.body.appendChild(form);
                        form.submit();
                    }
                },

                getStatusText(status) {
                    const statusMap = {
                        'belum_dikerjakan': 'Belum Dikerjakan',
                        'proses': 'Proses',
                        'selesai': 'Selesai'
                    };
                    return statusMap[status] || status;
                },

                formatDate(dateString) {
                    if (!dateString) return '-';
                    return new Date(dateString).toLocaleDateString('id-ID');
                }
            }
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const alpineData = Alpine.$data(document.body);
                if (alpineData.showViewModal) {
                    alpineData.closeViewModal();
                } else if (alpineData.showEditModal) {
                    alpineData.closeEditModal();
                } else if (alpineData.showAddModal) {
                    alpineData.closeAddModal();
                }
            }
        });
    </script>
</body>
</html> 