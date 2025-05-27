 <nav class="bg-white border-b border-gray-300">
    <div class="max-w-6xl mx-auto px-3">
        <div class="flex justify-between items-center h-14">
            <!-- Logo -->
            <div>
                <h1 class="text-xl font-bold text-blue-600">TodoApp</h1>
            </div>

            <!-- User Menu -->
            <div class="flex items-center space-x-3">
                @auth
                    <div class="flex items-center space-x-2">
                        <span class="text-gray-700 text-sm">{{ Auth::user()->name }}</span>
                        <a href="{{ route('logout') }}" 
                           class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                            Logout
                        </a>
                    </div>
                @else
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('login') }}" 
                           class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                            Login
                        </a>
                        <a href="{{ route('register') }}" 
                           class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
                            Register
                        </a>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</nav>