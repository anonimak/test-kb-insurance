<script setup>
import { ref, reactive } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const router = useRouter();
const authStore = useAuthStore();

const formData = reactive({
  login: "",
  password: "",
});

const errorMessage = ref("");
const isLoading = ref(false);

const handleLogin = async () => {
  try {
    errorMessage.value = "";
    isLoading.value = true;

    await authStore.login(formData);
    router.push("/dashboard");
  } catch (error) {
    errorMessage.value =
      error.response?.data?.messages ||
      error.response?.data?.message ||
      "Login failed. Please try again.";
  } finally {
    isLoading.value = false;
  }
};
</script>

<template>
  <div
    class="min-h-screen bg-gray-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8"
  >
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="w-full flex justify-center">
        <img src="/logo.png" class="h-16" alt="Vite logo" />
      </div>
      <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
        Sign in to your account
      </h2>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
        <form @submit.prevent="handleLogin" class="space-y-6">
          <div
            v-if="errorMessage"
            class="bg-red-50 border border-red-400 text-red-700 px-4 py-3 rounded relative"
            role="alert"
          >
            <span class="block sm:inline">{{ errorMessage }}</span>
          </div>

          <div>
            <label for="login" class="block text-sm font-medium text-gray-700">
              Email or Username
            </label>
            <div class="mt-1">
              <input
                id="login"
                name="login"
                type="text"
                required
                v-model="formData.login"
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              />
            </div>
          </div>

          <div>
            <label
              for="password"
              class="block text-sm font-medium text-gray-700"
            >
              Password
            </label>
            <div class="mt-1">
              <input
                id="password"
                name="password"
                type="password"
                required
                v-model="formData.password"
                class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              />
            </div>
          </div>

          <div>
            <button
              type="submit"
              :disabled="isLoading"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <span v-if="isLoading">Signing in...</span>
              <span v-else>Sign in</span>
            </button>
          </div>
        </form>

        <div class="mt-6">
          <div class="relative">
            <div class="flex justify-center text-sm">
              <router-link
                to="/register"
                class="font-medium text-blue-600 hover:text-blue-500"
              >
                Don't have an account? Register
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>