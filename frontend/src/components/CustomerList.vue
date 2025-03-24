<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import customerService from "@/services/customerService";

const router = useRouter();
const customers = ref([]);
const isLoading = ref(false);
const errorMessage = ref("");
const pagination = ref({
  currentPage: 1,
  totalPages: 0,
  totalRecords: 0,
  perPage: 10,
});
const coverageTypes = ref({});

onMounted(async () => {
  await loadCoverageTypes();
  await loadCustomers(1);
});

const loadCoverageTypes = async () => {
  try {
    const response = await customerService.getCoverageTypes();
    coverageTypes.value = response.data.data;
  } catch (error) {
    errorMessage.value = "Failed to load coverage types";
  }
};

const loadCustomers = async (page = 1) => {
  try {
    isLoading.value = true;
    errorMessage.value = "";

    const response = await customerService.getCustomers(page);
    customers.value = response.data.data;
    pagination.value = response.data.pagination;
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message ||
      "An error occurred while loading customers.";
    customers.value = [];
  } finally {
    isLoading.value = false;
  }
};

const formatDate = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  const options = { year: "numeric", month: "long", day: "numeric" };
  return date.toLocaleDateString("id-ID", options);
};

const formatPrice = (price) => {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    minimumFractionDigits: 0,
  }).format(price);
};

const getCoverageTypeName = (typeId) => {
  for (const [name, id] of Object.entries(coverageTypes.value)) {
    if (id === typeId) {
      return name;
    }
  }
  return "Unknown";
};

const handleEdit = (customerId) => {
  router.push(`/customers/${customerId}/edit`);
};

const handleDelete = async (customerId) => {
  if (!confirm("Are you sure you want to delete this customer coverage?")) {
    return;
  }

  try {
    await customerService.deleteCustomer(customerId);
    await loadCustomers(pagination.value.currentPage);
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || "Failed to delete customer coverage.";
  }
};

const handlePageChange = (page) => {
  loadCustomers(page);
};
</script>

<template>
  <div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Coverage History</h1>
      <router-link
        to="/customers/create"
        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
      >
        Add New Coverage
      </router-link>
    </div>

    <div
      v-if="errorMessage"
      class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"
    >
      {{ errorMessage }}
    </div>

    <div v-if="isLoading" class="flex justify-center items-center py-8">
      <div
        class="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-blue-500"
      ></div>
    </div>

    <div
      v-else-if="customers.length === 0"
      class="text-center py-8 text-gray-500"
    >
      No customer coverages found.
    </div>

    <div v-else>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Nama Tertanggung
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Periode Pertanggungan
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Tipe
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Harga Pertanggungan
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Risiko Pertanggungan
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Aksi
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="customer in customers" :key="customer.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  {{ customer.name }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ formatDate(customer.start_date_coverage) }} -
                  {{ formatDate(customer.end_date_coverage) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"
                >
                  {{ getCoverageTypeName(customer.type) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                {{ formatPrice(customer.price) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  <span
                    v-if="customer.is_risk_banjir"
                    class="mr-2 px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded"
                  >
                    Banjir
                  </span>
                  <span
                    v-if="customer.is_risk_gempa"
                    class="px-2 py-1 text-xs bg-orange-100 text-orange-800 rounded"
                  >
                    Gempa
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button
                  @click="router.push(`/customers/${customer.id}/report`)"
                  class="text-green-600 hover:text-green-900 mr-2"
                  title="View Report"
                >
                  Report
                </button>
                <button
                  @click="handleEdit(customer.id)"
                  class="text-blue-600 hover:text-blue-900 mr-2"
                >
                  Edit
                </button>
                <button
                  @click="handleDelete(customer.id)"
                  class="text-red-600 hover:text-red-900"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div
        v-if="pagination.totalPages > 1"
        class="flex justify-center items-center mt-6 space-x-2"
      >
        <button
          @click="handlePageChange(1)"
          :disabled="pagination.currentPage === 1"
          class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50"
        >
          First
        </button>
        <button
          @click="handlePageChange(pagination.currentPage - 1)"
          :disabled="pagination.currentPage === 1"
          class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50"
        >
          Prev
        </button>

        <span class="text-sm text-gray-700">
          Page {{ pagination.currentPage }} of {{ pagination.totalPages }}
        </span>

        <button
          @click="handlePageChange(pagination.currentPage + 1)"
          :disabled="pagination.currentPage === pagination.totalPages"
          class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50"
        >
          Next
        </button>
        <button
          @click="handlePageChange(pagination.totalPages)"
          :disabled="pagination.currentPage === pagination.totalPages"
          class="px-3 py-1 bg-gray-200 rounded disabled:opacity-50"
        >
          Last
        </button>
      </div>
    </div>
  </div>
</template>