<script setup>
import { ref, reactive, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import customerService from "@/services/customerService";
import { jsPDF } from "jspdf";
import { applyPlugin } from "jspdf-autotable";
import { usePremiumCalculator } from "@/composables/usePremiumCalculator";
import { Popover, PopoverButton, PopoverPanel } from "@headlessui/vue";
import { InformationCircleIcon } from "@heroicons/vue/24/solid";

applyPlugin(jsPDF);

const route = useRoute();
const router = useRouter();
const customerId = route.params.id;
const customer = ref(null);
const coverageTypes = ref({});
const isLoading = ref(true);
const errorMessage = ref("");

const logoPublicUrl = "/logo-kb.png";

const {
  premiumDetails,
  calculatePremium,
  formatCurrency,
  getCoverageTypeName,
  COVERAGE_RATES,
  RISK_RATES,
} = usePremiumCalculator();

onMounted(async () => {
  if (!customerId) {
    router.push("/customers");
    return;
  }

  try {
    isLoading.value = true;
    errorMessage.value = "";

    const coverageResponse = await customerService.getCoverageTypes();
    coverageTypes.value = coverageResponse.data.data;

    const response = await customerService.getCustomer(customerId);
    customer.value = response.data.data;

    calculatePremium(customer.value);
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || "Error loading customer data";
  } finally {
    isLoading.value = false;
  }
});

const formatDate = (dateString) => {
  if (!dateString) return "";
  const date = new Date(dateString);
  const options = { year: "numeric", month: "long", day: "numeric" };
  return date.toLocaleDateString("id-ID", options);
};

const getCoverageTypeNameDisplay = (typeId) => {
  return getCoverageTypeName(typeId, coverageTypes.value);
};

const generatePDF = () => {
  if (!customer.value) return;

  const doc = new jsPDF();
  const pageWidth = doc.internal.pageSize.getWidth();

  try {
    const img = new Image();
    img.crossOrigin = "Anonymous";

    img.onload = function () {
      doc.setFontSize(20);
      doc.setFont("helvetica", "bold");

      const canvas = document.createElement("canvas");
      canvas.width = img.width;
      canvas.height = img.height;
      const ctx = canvas.getContext("2d");
      ctx.drawImage(img, 0, 0);

      try {
        doc.addImage(canvas.toDataURL("image/png"), "PNG", 10, 5, 70, 26);
      } catch (err) {
        console.warn("Could not add logo to PDF:", err);
      }

      finishPdfGeneration(doc, pageWidth);
    };

    img.onerror = function () {
      console.warn("Could not load logo image");
      finishPdfGeneration(doc, pageWidth);
    };

    img.src = logoPublicUrl;
  } catch (error) {
    console.error("Error in PDF generation:", error);
    finishPdfGeneration(doc, pageWidth);
  }
};

const finishPdfGeneration = (doc, pageWidth) => {
  doc.setFontSize(10);
  doc.setFont("helvetica", "normal");
  doc.text(
    "Generated on: " + new Date().toLocaleDateString(),
    pageWidth - 60,
    30
  );

  doc.setFontSize(16);
  doc.setFont("helvetica", "bold");
  doc.text("General Information", 20, 40);
  doc.setLineWidth(0.5);
  doc.line(20, 42, pageWidth - 20, 42);

  doc.autoTable({
    startY: 43,
    body: [
      ["Nama Tertanggung", ": " + customer.value.name],
      [
        "Periode Pertanggungan",
        ": " +
          formatDate(customer.value.start_date_coverage) +
          " - " +
          formatDate(customer.value.end_date_coverage),
      ],
      ["Pertanggungan/Kendaraan", ": " + customer.value.coverage],
      ["Harga Pertanggungan", ": " + formatCurrency(customer.value.price)],
    ],
    theme: "plain",
    tableWidth: "auto",
    styles: { fontSize: 10 },
    margin: { left: 20, right: 20 },
    columnStyles: {
      0: { cellWidth: 60 },
      1: { cellWidth: 120 },
    },
  });

  doc.setFontSize(16);
  doc.setFont("helvetica", "bold");
  doc.text("Coverage Information", 20, 85);
  doc.setLineWidth(0.5);
  doc.line(20, 87, pageWidth - 20, 87);

  let riskText = ": ";
  const risks = [];
  if (customer.value.is_risk_banjir) risks.push("Banjir");
  if (customer.value.is_risk_gempa) risks.push("Gempa");
  riskText += risks.length ? risks.join(", ") : "None";

  doc.autoTable({
    startY: 90,
    body: [
      [
        "Jenis Pertanggungan",
        ": " + getCoverageTypeNameDisplay(customer.value.type),
      ],
      ["Risiko Pertanggungan", riskText],
      ["Durasi", ": " + premiumDetails.yearsCovered + " tahun"],
    ],
    theme: "plain",
    tableWidth: "auto",
    styles: { fontSize: 10 },
    margin: { left: 20, right: 20 },
    columnStyles: {
      0: { cellWidth: 60 },
      1: { cellWidth: 120 },
    },
  });

  doc.setFontSize(16);
  doc.setFont("helvetica", "bold");
  doc.text("Premium Calculation", 20, 125);
  doc.setLineWidth(0.5);
  doc.line(20, 127, pageWidth - 20, 127);

  const tableBody = [];

  tableBody.push([
    "Periode Pertanggungan",
    ": " +
      formatDate(customer.value.start_date_coverage) +
      " - " +
      formatDate(customer.value.end_date_coverage),
    "",
  ]);

  tableBody.push([
    "Premi Kendaraan",
    ": " + formatCurrency(premiumDetails.vehiclePremium),
    `${formatCurrency(customer.value.price)} × ${
      COVERAGE_RATES[customer.value.type]
    }`,
  ]);

  if (customer.value.is_risk_banjir) {
    tableBody.push([
      "Banjir",
      ": " + formatCurrency(premiumDetails.floodRiskPremium),
      `${formatCurrency(customer.value.price)} × ${
        RISK_RATES[customer.value.type].banjir
      }`,
    ]);
  }

  if (customer.value.is_risk_gempa) {
    tableBody.push([
      "Gempa",
      ": " + formatCurrency(premiumDetails.earthquakeRiskPremium),
      `${formatCurrency(customer.value.price)} × ${
        RISK_RATES[customer.value.type].gempa
      }`,
    ]);
  }

  tableBody.push([
    "Total Premi",
    ": " + formatCurrency(premiumDetails.totalPremium),
    "",
  ]);

  doc.autoTable({
    startY: 135,
    body: tableBody,
    theme: "plain",
    tableWidth: "auto",
    styles: { fontSize: 10 },
    margin: { left: 20, right: 20 },
    columnStyles: {
      0: { cellWidth: 60 },
      1: { cellWidth: 60 },
      2: { cellWidth: 60 },
    },
  });

  const finalY = doc.lastAutoTable.finalY + 20;

  // doc.save(`insurance_report_${customer.value.name.replace(/\s+/g, "_")}.pdf`);

  const pdfDataUri = doc.output("datauristring");
  const newTab = window.open();
  newTab?.document.write(
    `<iframe width='100%' height='100%' src='${pdfDataUri}'></iframe>`
  );
};
</script>

<template>
  <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
    <img src="/logo-kb.png" alt="KB Insurance" class="h-20 mr-3" />
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold">Insurance Coverage Report</h1>
      <div class="space-x-2">
        <button
          @click="router.push('/customers')"
          class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
        >
          Back to List
        </button>
        <button
          @click="generatePDF"
          class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
        >
          Export PDF
        </button>
      </div>
    </div>

    <div v-if="isLoading" class="flex justify-center items-center py-20">
      <div
        class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"
      ></div>
    </div>

    <div
      v-else-if="errorMessage"
      class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"
    >
      {{ errorMessage }}
    </div>

    <div v-else-if="customer" class="space-y-8">
      <section>
        <h2 class="text-xl font-semibold border-b pb-2 mb-4">
          General Information
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <p class="text-sm text-gray-500">Nama Tertanggung</p>
            <p class="font-medium">{{ customer.name }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500">Periode Pertanggungan</p>
            <p class="font-medium">
              {{ formatDate(customer.start_date_coverage) }} -
              {{ formatDate(customer.end_date_coverage) }}
            </p>
          </div>
          <div>
            <p class="text-sm text-gray-500">Pertanggungan/Kendaraan</p>
            <p class="font-medium">{{ customer.coverage }}</p>
          </div>
          <div>
            <p class="text-sm text-gray-500">Harga Pertanggungan</p>
            <p class="font-medium">{{ formatCurrency(customer.price) }}</p>
          </div>
        </div>
      </section>

      <section>
        <h2 class="text-xl font-semibold border-b pb-2 mb-4">
          Coverage Information
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <p class="text-sm text-gray-500">Jenis Pertanggungan</p>
            <p class="font-medium">
              {{ getCoverageTypeNameDisplay(customer.type) }}
            </p>
          </div>
          <div>
            <p class="text-sm text-gray-500">Durasi</p>
            <p class="font-medium">
              {{ premiumDetails.yearsCovered }} tahun{{
                premiumDetails.yearsCovered > 1 ? "" : ""
              }}
            </p>
          </div>

          <div>
            <p class="text-sm text-gray-500">Risiko Pertanggungan</p>
            <div class="flex space-x-2">
              <span
                v-if="customer.is_risk_banjir"
                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800"
              >
                Banjir
              </span>
              <span
                v-if="customer.is_risk_gempa"
                class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-orange-100 text-orange-800"
              >
                Gempa
              </span>
              <span
                v-if="!customer.is_risk_banjir && !customer.is_risk_gempa"
                class="text-gray-500"
                >None</span
              >
            </div>
          </div>
        </div>
      </section>
      <section>
        <h2 class="text-xl font-semibold border-b pb-2 mb-4">
          Premium Calculation
        </h2>

        <div
          class="bg-white shadow ring-1 ring-black ring-opacity-5 md:rounded-lg"
        >
          <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
              <tr>
                <th
                  scope="col"
                  class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6"
                >
                  Periode Pertanggungan
                </th>
                <th
                  scope="col"
                  class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900"
                >
                  <p class="font-medium">
                    {{ formatDate(customer.start_date_coverage) }} -
                    {{ formatDate(customer.end_date_coverage) }}
                  </p>
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
              <tr>
                <td
                  class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 flex items-center"
                >
                  <span>Premi Kendaraan</span>
                  <Popover class="relative ml-1">
                    <PopoverButton class="focus:outline-none">
                      <InformationCircleIcon class="h-4 w-4 text-blue-500" />
                    </PopoverButton>
                    <PopoverPanel
                      class="absolute left-1/2 z-10 mt-3 w-60 max-w-sm -translate-x-1/2 transform px-4 sm:px-0"
                    >
                      <div
                        class="overflow-hidden rounded-lg shadow-lg ring-1 ring-black/5"
                      >
                        <div
                          class="relative grid gap-2 bg-white p-4 grid-cols-2"
                        >
                          <div class="text-xs text-start text-gray-700">
                            Harga <span class="font-bold">(H)</span>
                          </div>
                          <div class="text-xs text-end text-gray-700">
                            {{ formatCurrency(customer.price) }}
                          </div>
                          <div class="text-xs text-start text-gray-700">
                            Coverage Type <span class="font-bold">(T)</span>
                          </div>
                          <div class="text-xs text-end text-gray-700">
                            {{ COVERAGE_RATES[customer.type] }}
                          </div>
                          <div class="text-xs text-start text-gray-700">
                            Tahun <span class="font-bold">(th)</span>
                          </div>
                          <div class="text-xs text-end text-gray-700">
                            {{ premiumDetails.yearsCovered }}
                          </div>
                          <div class="col-span-2 w-full border"></div>
                          <div
                            class="text-xs font-bold text-start text-gray-700"
                          >
                            (H×T×th)
                          </div>
                          <div
                            class="text-xs flex font-bold justify-end text-gray-700"
                          >
                            {{ formatCurrency(premiumDetails.vehiclePremium) }}
                          </div>
                        </div>
                      </div>
                    </PopoverPanel>
                  </Popover>
                </td>

                <td
                  class="whitespace-nowrap px-3 py-4 text-sm text-right font-medium"
                >
                  {{ formatCurrency(premiumDetails.vehiclePremium) }}
                </td>
              </tr>

              <tr v-if="customer.is_risk_banjir">
                <td
                  class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 flex items-center"
                >
                  <span>Banjir</span>
                  <Popover class="relative ml-1">
                    <PopoverButton class="focus:outline-none">
                      <InformationCircleIcon class="h-4 w-4 text-blue-500" />
                    </PopoverButton>
                    <PopoverPanel
                      class="absolute left-1/2 z-10 mt-3 w-60 max-w-sm -translate-x-1/2 transform px-4 sm:px-0"
                    >
                      <div
                        class="overflow-hidden rounded-lg shadow-lg ring-1 ring-black/5"
                      >
                        <div
                          class="relative grid gap-2 bg-white p-4 grid-cols-2"
                        >
                          <div class="text-xs text-start text-gray-700">
                            Harga <span class="font-bold">(H)</span>
                          </div>
                          <div class="text-xs text-end text-gray-700">
                            {{ formatCurrency(customer.price) }}
                          </div>
                          <div class="text-xs text-start text-gray-700">
                            Flood Risk Rate <span class="font-bold">(B)</span>
                          </div>
                          <div class="text-xs text-end text-gray-700">
                            {{ RISK_RATES[customer.type].banjir }}
                          </div>
                          <div class="text-xs text-start text-gray-700">
                            Tahun <span class="font-bold">(th)</span>
                          </div>
                          <div class="text-xs text-end text-gray-700">
                            {{ premiumDetails.yearsCovered }}
                          </div>
                          <div class="col-span-2 w-full border"></div>
                          <div
                            class="text-xs font-bold text-start text-gray-700"
                          >
                            (H×B×th)
                          </div>
                          <div
                            class="text-xs flex font-bold justify-end text-gray-700"
                          >
                            {{
                              formatCurrency(premiumDetails.floodRiskPremium)
                            }}
                          </div>
                        </div>
                      </div>
                    </PopoverPanel>
                  </Popover>
                </td>

                <td
                  class="whitespace-nowrap px-3 py-4 text-sm text-right font-medium"
                >
                  {{ formatCurrency(premiumDetails.floodRiskPremium) }}
                </td>
              </tr>

              <tr v-if="customer.is_risk_gempa">
                <td
                  class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6 flex items-center"
                >
                  <span>Gempa</span>
                  <Popover class="relative ml-1">
                    <PopoverButton class="focus:outline-none">
                      <InformationCircleIcon class="h-4 w-4 text-blue-500" />
                    </PopoverButton>
                    <PopoverPanel
                      class="absolute left-1/2 z-10 mt-3 w-60 max-w-sm -translate-x-1/2 transform px-4 sm:px-0"
                    >
                      <div
                        class="overflow-hidden rounded-lg shadow-lg ring-1 ring-black/5"
                      >
                        <div
                          class="relative grid gap-2 bg-white p-4 grid-cols-2"
                        >
                          <div class="text-xs text-start text-gray-700">
                            Harga <span class="font-bold">(H)</span>
                          </div>
                          <div class="text-xs text-end text-gray-700">
                            {{ formatCurrency(customer.price) }}
                          </div>
                          <div class="text-xs text-start text-gray-700">
                            Earthquake Rate <span class="font-bold">(G)</span>
                          </div>
                          <div class="text-xs text-end text-gray-700">
                            {{ RISK_RATES[customer.type].gempa }}
                          </div>
                          <div class="text-xs text-start text-gray-700">
                            Tahun <span class="font-bold">(th)</span>
                          </div>
                          <div class="text-xs text-end text-gray-700">
                            {{ premiumDetails.yearsCovered }}
                          </div>
                          <div class="col-span-2 w-full border"></div>
                          <div
                            class="text-xs font-bold text-start text-gray-700"
                          >
                            (H×G×th)
                          </div>
                          <div
                            class="text-xs flex font-bold justify-end text-gray-700"
                          >
                            {{
                              formatCurrency(
                                premiumDetails.earthquakeRiskPremium
                              )
                            }}
                          </div>
                        </div>
                      </div>
                    </PopoverPanel>
                  </Popover>
                </td>
                <td
                  class="whitespace-nowrap px-3 py-4 text-sm text-right font-medium"
                >
                  {{ formatCurrency(premiumDetails.earthquakeRiskPremium) }}
                </td>
              </tr>

              <tr class="bg-gray-50">
                <td
                  class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-bold text-gray-900 sm:pl-6 flex items-center"
                >
                  <span>Total Premi</span>
                  <Popover class="relative ml-1">
                    <PopoverButton class="focus:outline-none">
                      <InformationCircleIcon class="h-4 w-4 text-blue-500" />
                    </PopoverButton>
                    <PopoverPanel
                      class="absolute left-1/2 z-10 mt-3 w-72 max-w-sm -translate-x-1/2 transform px-4 sm:px-0"
                    >
                      <div
                        class="overflow-hidden rounded-lg shadow-lg ring-1 ring-black/5"
                      >
                        <div class="relative grid gap-2 bg-white p-4">
                          <div
                            class="text-xs font-bold text-center text-gray-700 pb-2"
                          >
                            Total Premium Breakdown
                          </div>
                          <div class="grid grid-cols-2 gap-2">
                            <div class="text-xs text-start text-gray-700">
                              Vehicle Premium
                            </div>
                            <div class="text-xs text-end text-gray-700">
                              {{
                                formatCurrency(premiumDetails.vehiclePremium)
                              }}
                            </div>

                            <template v-if="customer.is_risk_banjir">
                              <div class="text-xs text-start text-gray-700">
                                Flood Risk Premium
                              </div>
                              <div class="text-xs text-end text-gray-700">
                                {{
                                  formatCurrency(
                                    premiumDetails.floodRiskPremium
                                  )
                                }}
                              </div>
                            </template>

                            <template v-if="customer.is_risk_gempa">
                              <div class="text-xs text-start text-gray-700">
                                Earthquake Risk Premium
                              </div>
                              <div class="text-xs text-end text-gray-700">
                                {{
                                  formatCurrency(
                                    premiumDetails.earthquakeRiskPremium
                                  )
                                }}
                              </div>
                            </template>

                            <div class="col-span-2 w-full border my-1"></div>

                            <div
                              class="text-xs font-bold text-start text-gray-700"
                            >
                              Total Premium
                            </div>
                            <div
                              class="text-xs font-bold text-end text-gray-700"
                            >
                              {{ formatCurrency(premiumDetails.totalPremium) }}
                            </div>
                          </div>
                        </div>
                      </div>
                    </PopoverPanel>
                  </Popover>
                </td>
                <td
                  class="whitespace-nowrap px-3 py-4 text-sm text-right font-bold"
                >
                  {{ formatCurrency(premiumDetails.totalPremium) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="mt-4 p-4 bg-gray-50 border border-gray-200 rounded-md">
          <h3 class="text-sm font-medium text-gray-700 mb-2">
            Premi Kalkulasi Formula:
          </h3>
          <ul class="space-y-2 text-sm text-gray-600">
            <li>
              <strong>Premi Kendaraan</strong> = Harga Pertanggungan × Rate
              Jenis Pertanggungan × Tahun Pertanggungan
            </li>
            <li v-if="customer.is_risk_banjir">
              <strong>Banjir</strong> = Harga Pertanggungan × Rate Banjir ×
              Tahun Pertanggungan
            </li>
            <li v-if="customer.is_risk_gempa">
              <strong>Gempa</strong> = Harga Pertanggungan × Rate Gempa × Tahun
              Pertanggungan
            </li>
            <li>
              <strong>Total Premi</strong> = Premi Kendaraan + Banjir + Gempa
            </li>
          </ul>
        </div>
      </section>
    </div>
  </div>
</template>