import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import LoginView from "@/views/LoginView.vue";
import RegisterView from "@/views/RegisterView.vue";
import DashboardView from "@/views/DashboardView.vue";
import CustomerListView from "@/views/CustomerListView.vue";
import CustomerCreateView from "@/views/CustomerCreateView.vue";
import CustomerEditView from "@/views/CustomerEditView.vue";
import CustomerReportView from "@/views/CustomerReportView.vue";
import NotFoundView from "@/views/NotFoundView.vue";

const router = createRouter({
  history: createWebHistory("/"),
  routes: [
    {
      path: "/login",
      name: "login",
      component: LoginView,
      meta: { requiresGuest: true },
    },
    {
      path: "/register",
      name: "register",
      component: RegisterView,
      meta: { requiresGuest: true },
    },
    {
      path: "/dashboard",
      name: "dashboard",
      component: DashboardView,
      meta: { requiresAuth: true },
    },
    {
      path: "/",
      name: "home",
      component: DashboardView,
      meta: { requiresAuth: true },
    },
    // Customer routes
    {
      path: "/customers",
      name: "customers",
      component: CustomerListView,
      meta: { requiresAuth: true },
    },
    {
      path: "/customers/create",
      name: "customers-create",
      component: CustomerCreateView,
      meta: { requiresAuth: true },
    },
    {
      path: "/customers/:id/edit",
      name: "customers-edit",
      component: CustomerEditView,
      meta: { requiresAuth: true },
    },
    // report route
    {
      path: "/customers/:id/report",
      name: "customers-report",
      component: CustomerReportView,
      meta: { requiresAuth: true },
    },
    {
      path: "/:catchAll(.*)",
      name: "notFound",
      component: NotFoundView,
    },
  ],
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  const isAuthenticated = authStore.isAuthenticated;

  if (to.meta.requiresAuth && !isAuthenticated) {
    return next({ name: "login", query: { redirect: to.fullPath } });
  }

  if (to.meta.requiresGuest && isAuthenticated) {
    return next({ name: "dashboard" });
  }

  next();
});

export default router;
