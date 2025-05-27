<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

  <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Login Akun</h2>
    <form action="{{url('/login-post')}}" method="POST" class="space-y-4">
    @csrf

      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" id="email" name="email" required
          class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-400">
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <input type="password" id="password" name="password" required
          class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-400">
      </div>

      <button type="submit"
        class="w-full bg-green-500 text-white py-2 rounded-lg font-semibold hover:bg-green-600 transition-colors">
        Login
      </button>
    </form>

    <p class="text-center text-sm text-gray-600 mt-4">
      Belum punya akun?
      <a href="/register" class="text-green-600 hover:underline">Daftar di sini</a>
    </p>
  </div>

</body>
</html>
