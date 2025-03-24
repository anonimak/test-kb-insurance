<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-md w-full bg-white shadow-lg rounded-lg p-8 text-center">
      <h1 class="text-4xl font-bold text-red-500 mb-4">404</h1>
      <h2 class="text-2xl font-semibold text-gray-800 mb-6">Page Not Found</h2>
      <p class="text-gray-600 mb-8">
        The page you are looking for doesn't exist or has been moved.
      </p>
      <router-link
        to="/"
        class="inline-block bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition-colors"
      >
        Return to Home
      </router-link>
    </div>
  </div>
</template>