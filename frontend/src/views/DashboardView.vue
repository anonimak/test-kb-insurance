<script setup>
import { ref, onMounted } from "vue";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();
const user = ref(null);
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
  try {
    user.value = await authStore.getProfile();
  } catch (err) {
    error.value = "Failed to load profile data.";
  } finally {
    loading.value = false;
  }
});
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
      </div>
    </header>
    <main>
      <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
          <div class="bg-white p-6 rounded-lg shadow-md">
            <div v-if="loading">
              <p class="text-gray-500">Loading profile...</p>
            </div>

            <div v-else-if="error">
              <p class="text-red-500">{{ error }}</p>
            </div>

            <div v-else>
              <h2 class="text-lg font-medium text-gray-900">
                Welcome, {{ user.name }}!
              </h2>
              <div class="mt-6 border-t border-gray-100">
                <dl class="divide-y divide-gray-100">
                  <div
                    class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0"
                  >
                    <dt class="text-sm font-medium leading-6 text-gray-900">
                      Username
                    </dt>
                    <dd
                      class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0"
                    >
                      {{ user.username }}
                    </dd>
                  </div>
                  <div
                    class="px-4 py-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0"
                  >
                    <dt class="text-sm font-medium leading-6 text-gray-900">
                      Email address
                    </dt>
                    <dd
                      class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0"
                    >
                      {{ user.email }}
                    </dd>
                  </div>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>