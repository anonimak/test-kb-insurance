<script setup>
import { computed, onMounted } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useRouter } from "vue-router";

const authStore = useAuthStore();
const router = useRouter();

const isLoggedIn = computed(() => authStore.isAuthenticated);

const handleLogout = async () => {
  await authStore.logout();
  router.push("/login");
};

onMounted(() => {
  authStore.initializeAuth();
});
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <header class="bg-white shadow-md">
      <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex justify-between items-center">
          <div class="flex items-center space-x-2">
            <!-- <img src="/logo-kb.png" class="h-8 mr-3" alt="Vue logo" /> -->
            <img src="/logo.png" class="h-8" alt="Vite logo" />
            <span class="font-bold text-xl"> Insurance Indonesia</span>
          </div>
          <div class="flex space-x-6">
            <template v-if="isLoggedIn">
              <router-link
                to="/dashboard"
                class="text-gray-700 hover:text-blue-600 transition-colors"
                active-class="text-blue-600 font-medium"
              >
                Dashboard
              </router-link>
              <router-link
                to="/customers"
                class="text-gray-700 hover:text-blue-600 transition-colors"
                active-class="text-blue-600 font-medium"
              >
                Coverage
              </router-link>
              <button
                @click="handleLogout"
                class="text-gray-700 hover:text-blue-600 transition-colors"
              >
                Logout
              </button>
            </template>
            <template v-else>
              <router-link
                to="/login"
                class="text-gray-700 hover:text-blue-600 transition-colors"
                active-class="text-blue-600 font-medium"
              >
                Login
              </router-link>
              <router-link
                to="/register"
                class="text-gray-700 hover:text-blue-600 transition-colors"
                active-class="text-blue-600 font-medium"
              >
                Register
              </router-link>
            </template>
          </div>
        </div>
      </nav>
    </header>

    <main>
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>
    </main>
  </div>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
