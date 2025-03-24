<script setup>
import { ref, reactive, onMounted } from "vue";
import { Popover, PopoverButton, PopoverPanel } from "@headlessui/vue";
import { InformationCircleIcon } from "@heroicons/vue/24/solid";
import { useRouter } from "vue-router";
import customerService from "@/services/customerService";
import { usePremiumCalculator } from "@/composables/usePremiumCalculator";

const props = defineProps({
  customerId: {
    type: [Number, String],
    default: null,
  },
});

const router = useRouter();
const isLoading = ref(false);
const errorMessage = ref("");
const coverageTypes = ref({});
const isEdit = ref(!!props.customerId);

const customer = reactive({
  name: "",
  start_date_coverage: "",
  end_date_coverage: "",
  coverage: "",
  price: "",
  type: "",
  is_risk_banjir: false,
  is_risk_gempa: false,
});

const {
  premiumDetails,
  formattedPremium,
  formatCurrency,
  getCoverageTypeName,
  COVERAGE_RATES,
  RISK_RATES,
} = usePremiumCalculator({
  customerData: customer,
  autoCompute: true,
});

function formatDateForInput(dateString) {
  if (!dateString) return "";
  const date = new Date(dateString);
  return date.toISOString().split("T")[0];
}

onMounted(async () => {
  try {
    isLoading.value = true;

    const coverageResponse = await customerService.getCoverageTypes();
    coverageTypes.value = coverageResponse.data.data;

    if (props.customerId) {
      const response = await customerService.getCustomer(props.customerId);
      const customerData = response.data.data;

      customer.name = customerData.name;
      customer.start_date_coverage = formatDateForInput(
        customerData.start_date_coverage
      );
      customer.end_date_coverage = formatDateForInput(
        customerData.end_date_coverage
      );
      customer.coverage = customerData.coverage;
      customer.price = customerData.price;
      customer.type = customerData.type;
      customer.is_risk_banjir = customerData.is_risk_banjir ? true : false;
      customer.is_risk_gempa = customerData.is_risk_gempa ? true : false;
    }
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || "An error occurred while loading data.";
  } finally {
    isLoading.value = false;
  }
});

const handleSubmit = async () => {
  try {
    isLoading.value = true;
    errorMessage.value = "";

    const customerData = {
      ...customer,
      premium_yearly: premiumDetails.annualPremium,
      premium_total: premiumDetails.totalPremium,
    };

    if (props.customerId) {
      await customerService.updateCustomer(props.customerId, customerData);
    } else {
      await customerService.createCustomer(customerData);
    }

    router.push("/customers");
  } catch (error) {
    errorMessage.value =
      error.response?.data?.messages ||
      error.response?.data?.message ||
      "An error occurred while saving data.";
  } finally {
    isLoading.value = false;
  }
};

const getCoverageTypeNameDisplay = (typeId) => {
  return getCoverageTypeName(typeId, coverageTypes.value);
};
</script>

<template>
  <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <h1 class="text-2xl font-bold mb-6">
      {{ isEdit ? "Edit Customer Coverage" : "Add Customer Coverage" }}
    </h1>

    <div>
      <h3 class="text-lg font-semibold">General Information</h3>
      <div class="w-full border mt-2 mb-4"></div>
    </div>
    <div
      v-if="errorMessage"
      class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"
    >
      {{ errorMessage }}
    </div>

    <form @submit.prevent="handleSubmit" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1" for="name"
          >Nama Tertanggung</label
        >
        <input
          id="name"
          v-model="customer.name"
          type="text"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-0" for="name"
          >Periode Tertanggung</label
        >
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label
            class="block text-sm font-medium text-gray-700 mb-1"
            for="start_date"
            >Tanggal Mulai</label
          >
          <input
            id="start_date"
            v-model="customer.start_date_coverage"
            type="date"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          />
        </div>

        <div>
          <label
            class="block text-sm font-medium text-gray-700 mb-1"
            for="end_date"
            >Tanggal Selesai</label
          >
          <input
            id="end_date"
            v-model="customer.end_date_coverage"
            type="date"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          />
        </div>
      </div>

      <div>
        <label
          class="block text-sm font-medium text-gray-700 mb-1"
          for="coverage"
          >Kendaraan</label
        >
        <input
          id="coverage"
          v-model="customer.coverage"
          type="text"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1" for="price"
          >Harga Pertanggungan</label
        >
        <input
          id="price"
          v-model="customer.price"
          type="number"
          step="0.01"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
        />
      </div>

      <div class="mt-8">
        <h3 class="text-lg font-semibold">Coverage Information</h3>
        <div class="w-full border mt-2 mb-4"></div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1" for="type"
          >Tipe Pertanggungan</label
        >
        <select
          id="type"
          v-model="customer.type"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
        >
          <option value="">Select Tipe Pertanggungan</option>
          <option
            v-for="(value, name) in coverageTypes"
            :key="value"
            :value="value"
          >
            {{ name }}
          </option>
        </select>
      </div>

      <div class="space-y-2">
        <div class="flex items-center">
          <input
            id="risk_banjir"
            v-model="customer.is_risk_banjir"
            type="checkbox"
            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            :disabled="customer.type == 2"
          />
          <label for="risk_banjir" class="ml-2 text-sm text-gray-700"
            >Banjir{{
              customer.type == 2 ? " (Not available for TLO)" : ""
            }}</label
          >
        </div>

        <div class="flex items-center">
          <input
            id="risk_gempa"
            v-model="customer.is_risk_gempa"
            type="checkbox"
            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            :disabled="customer.type == 2"
          />
          <label for="risk_gempa" class="ml-2 text-sm text-gray-700"
            >Gempa{{
              customer.type == 2 ? " (Not available for TLO)" : ""
            }}</label
          >
        </div>
      </div>

      <div
        v-if="customer.price && customer.type"
        class="mt-6 bg-gray-50 border rounded-md p-4"
      >
        <h3 class="text-lg font-medium text-gray-900 mb-4">
          Premium Calculation
        </h3>
        <div class="space-y-3">
          <div class="flex justify-between items-center">
            <div class="flex items-center space-x-1">
              <span class="font-medium">Premi Kendairaan </span>

              <Popover class="relative">
                <PopoverButton class="flex items-center focus:outline-none">
                  <InformationCircleIcon
                    class="h-4 w-4 text-blue-500 hover:text-blue-600"
                  />
                </PopoverButton>
                <PopoverPanel
                  class="absolute z-10 mt-3 w-60 -translate-x-1/2 transform px-4 sm:px-0"
                >
                  <div
                    class="overflow-hidden rounded-lg shadow-lg ring-1 ring-black/5"
                  >
                    <div class="relative grid gap-2 bg-white p-4 grid-cols-2">
                      <div class="text-xs text-start text-gray-700">
                        Premi Kendaraan <span class="font-bold">(V)</span>
                      </div>
                      <div class="text-xs text-end text-gray-700">
                        {{ formatCurrency(customer.price) }}
                      </div>
                      <div class="text-xs text-start text-gray-700">
                        Coverage Rate <span class="font-bold">(R)</span>
                      </div>
                      <div class="text-xs text-end text-gray-700">
                        {{ COVERAGE_RATES[customer.type] }}
                      </div>
                      <div class="text-xs text-start text-gray-700">
                        Durasi <span class="font-bold">(Y)</span>
                      </div>
                      <div class="text-xs text-end text-gray-700">
                        {{ premiumDetails.yearsCovered }} years
                      </div>
                      <div class="col-span-2 w-full border my-1"></div>
                      <div class="text-xs font-bold text-start text-gray-700">
                        Formula: V × R × Y =
                      </div>
                      <div class="text-xs font-bold text-end text-gray-700">
                        {{ formatCurrency(premiumDetails.vehiclePremium) }}
                      </div>
                    </div>
                  </div>
                </PopoverPanel>
              </Popover>
            </div>
            <div class="font-medium">{{ formattedPremium.vehiclePremium }}</div>
          </div>

          <div
            v-if="customer.is_risk_banjir"
            class="flex justify-between items-center"
          >
            <div class="flex items-center space-x-1">
              <span class="font-medium">Banjir (Risk)</span>

              <Popover class="relative">
                <PopoverButton class="flex items-center focus:outline-none">
                  <InformationCircleIcon
                    class="h-4 w-4 text-blue-500 hover:text-blue-600"
                  />
                </PopoverButton>
                <PopoverPanel
                  class="absolute z-10 mt-3 w-60 -translate-x-1/2 transform px-4 sm:px-0"
                >
                  <div
                    class="overflow-hidden rounded-lg shadow-lg ring-1 ring-black/5"
                  >
                    <div class="relative grid gap-2 bg-white p-4 grid-cols-2">
                      <div class="text-xs text-start text-gray-700">
                        Premi Kendaraan <span class="font-bold">(V)</span>
                      </div>
                      <div class="text-xs text-end text-gray-700">
                        {{ formatCurrency(customer.price) }}
                      </div>
                      <div class="text-xs text-start text-gray-700">
                        Flood Risk Rate <span class="font-bold">(FR)</span>
                      </div>
                      <div class="text-xs text-end text-gray-700">
                        {{ RISK_RATES[customer.type].banjir }}
                      </div>
                      <div class="text-xs text-start text-gray-700">
                        Durasi <span class="font-bold">(Y)</span>
                      </div>
                      <div class="text-xs text-end text-gray-700">
                        {{ premiumDetails.yearsCovered }} years
                      </div>
                      <div class="col-span-2 w-full border my-1"></div>
                      <div class="text-xs font-bold text-start text-gray-700">
                        Formula: V × FR × Y =
                      </div>
                      <div class="text-xs font-bold text-end text-gray-700">
                        {{ formatCurrency(premiumDetails.floodRiskPremium) }}
                      </div>
                    </div>
                  </div>
                </PopoverPanel>
              </Popover>
            </div>
            <div class="font-medium">
              {{ formattedPremium.floodRiskPremium }}
            </div>
          </div>

          <div
            v-if="customer.is_risk_gempa"
            class="flex justify-between items-center"
          >
            <div class="flex items-center space-x-1">
              <span class="font-medium">Gempa Bumi (Risk)</span>

              <Popover class="relative">
                <PopoverButton class="flex items-center focus:outline-none">
                  <InformationCircleIcon
                    class="h-4 w-4 text-blue-500 hover:text-blue-600"
                  />
                </PopoverButton>
                <PopoverPanel
                  class="absolute z-10 mt-3 w-60 -translate-x-1/2 transform px-4 sm:px-0"
                >
                  <div
                    class="overflow-hidden rounded-lg shadow-lg ring-1 ring-black/5"
                  >
                    <div class="relative grid gap-2 bg-white p-4 grid-cols-2">
                      <div class="text-xs text-start text-gray-700">
                        Premi Kendaraan <span class="font-bold">(V)</span>
                      </div>
                      <div class="text-xs text-end text-gray-700">
                        {{ formatCurrency(customer.price) }}
                      </div>
                      <div class="text-xs text-start text-gray-700">
                        Earthquake Risk Rate <span class="font-bold">(ER)</span>
                      </div>
                      <div class="text-xs text-end text-gray-700">
                        {{ RISK_RATES[customer.type].gempa }}
                      </div>
                      <div class="text-xs text-start text-gray-700">
                        Durasi <span class="font-bold">(Y)</span>
                      </div>
                      <div class="text-xs text-end text-gray-700">
                        {{ premiumDetails.yearsCovered }} years
                      </div>
                      <div class="col-span-2 w-full border my-1"></div>
                      <div class="text-xs font-bold text-start text-gray-700">
                        Formula: V × ER × Y =
                      </div>
                      <div class="text-xs font-bold text-end text-gray-700">
                        {{
                          formatCurrency(premiumDetails.earthquakeRiskPremium)
                        }}
                      </div>
                    </div>
                  </div>
                </PopoverPanel>
              </Popover>
            </div>
            <div class="font-medium">
              {{ formattedPremium.earthquakeRiskPremium }}
            </div>
          </div>

          <div class="flex justify-between pt-2 border-t items-center">
            <div class="flex items-center space-x-1">
              <span class="font-bold text-lg">Total Premium:</span>

              <Popover class="relative">
                <PopoverButton class="flex items-center focus:outline-none">
                  <InformationCircleIcon
                    class="h-4 w-4 text-blue-500 hover:text-blue-600"
                  />
                </PopoverButton>
                <PopoverPanel
                  class="absolute z-10 mt-3 w-72 -translate-x-1/2 transform px-4 sm:px-0"
                >
                  <div
                    class="overflow-hidden rounded-lg shadow-lg ring-1 ring-black/5"
                  >
                    <div class="relative grid gap-2 bg-white p-4 grid-cols-2">
                      <div class="text-xs text-start text-gray-700">
                        Premi Kendaraan
                      </div>
                      <div class="text-xs text-end text-gray-700">
                        {{ formatCurrency(premiumDetails.vehiclePremium) }}
                      </div>

                      <div
                        v-if="customer.is_risk_banjir"
                        class="text-xs text-start text-gray-700"
                      >
                        Banjir (Risk)
                      </div>
                      <div
                        v-if="customer.is_risk_banjir"
                        class="text-xs text-end text-gray-700"
                      >
                        {{ formatCurrency(premiumDetails.floodRiskPremium) }}
                      </div>

                      <div
                        v-if="customer.is_risk_gempa"
                        class="text-xs text-start text-gray-700"
                      >
                        Gempa Bumi (Risk)
                      </div>
                      <div
                        v-if="customer.is_risk_gempa"
                        class="text-xs text-end text-gray-700"
                      >
                        {{
                          formatCurrency(premiumDetails.earthquakeRiskPremium)
                        }}
                      </div>

                      <div class="col-span-2 w-full border my-1"></div>
                      <div class="text-xs font-bold text-start text-gray-700">
                        Total Premium
                      </div>
                      <div class="text-xs font-bold text-end text-gray-700">
                        {{ formatCurrency(premiumDetails.totalPremium) }}
                      </div>
                    </div>
                  </div>
                </PopoverPanel>
              </Popover>
            </div>
            <span class="font-bold text-lg text-blue-700">
              {{ formattedPremium.totalPremium }}
            </span>
          </div>

          <div
            v-if="premiumDetails.yearsCovered > 1"
            class="flex justify-between text-sm text-gray-600 items-center"
          >
            <div class="flex items-center space-x-1">
              <span>Annual Payment:</span>

              <Popover class="relative">
                <PopoverButton class="flex items-center focus:outline-none">
                  <InformationCircleIcon
                    class="h-4 w-4 text-blue-500 hover:text-blue-600"
                  />
                </PopoverButton>
                <PopoverPanel
                  class="absolute z-10 mt-3 w-60 -translate-x-1/2 transform px-4 sm:px-0"
                >
                  <div
                    class="overflow-hidden rounded-lg shadow-lg ring-1 ring-black/5"
                  >
                    <div class="relative grid gap-2 bg-white p-4 grid-cols-2">
                      <div class="text-xs text-start text-gray-700">
                        Total Premium <span class="font-bold">(TP)</span>
                      </div>
                      <div class="text-xs text-end text-gray-700">
                        {{ formatCurrency(premiumDetails.totalPremium) }}
                      </div>
                      <div class="text-xs text-start text-gray-700">
                        Durasi <span class="font-bold">(Y)</span>
                      </div>
                      <div class="text-xs text-end text-gray-700">
                        {{ premiumDetails.yearsCovered }} years
                      </div>
                      <div class="col-span-2 w-full border my-1"></div>
                      <div class="text-xs font-bold text-start text-gray-700">
                        Formula: TP ÷ Y =
                      </div>
                      <div class="text-xs font-bold text-end text-gray-700">
                        {{ formatCurrency(premiumDetails.annualPremium) }}
                      </div>
                    </div>
                  </div>
                </PopoverPanel>
              </Popover>
            </div>
            <span>{{ formattedPremium.annualPremium }}</span>
          </div>
        </div>
      </div>

      <div class="flex justify-end space-x-3 pt-4">
        <button
          type="button"
          @click="router.push('/customers')"
          class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          Cancel
        </button>
        <button
          type="submit"
          :disabled="isLoading"
          class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          {{ isLoading ? "Saving..." : "Save" }}
        </button>
      </div>
    </form>
  </div>
</template>