import { reactive, computed, watch, toRefs } from "vue";

/**
 * Coverage rate constants
 */
export const COVERAGE_RATES = {
  1: 0.0015, // Comprehensive rate
  2: 0.0005, // Total Loss Only rate
};

/**
 * Risk rate constants
 */
export const RISK_RATES = {
  1: {
    // Comprehensive
    banjir: 0.0005,
    gempa: 0.0002,
  },
  2: {
    // Total Loss Only
    banjir: 0,
    gempa: 0,
  },
};

/**
 * Premium calculator composable
 * @param {Object} options Configuration options
 * @param {Object|Ref} options.customerData Reference to customer data
 * @param {boolean} options.autoCompute Whether to compute automatically when data changes
 * @returns {Object} Premium calculator API
 */
export function usePremiumCalculator(options = {}) {
  const { customerData = {}, autoCompute = true } = options;

  // Premium calculation state
  const premiumDetails = reactive({
    yearsCovered: 0,
    vehiclePremium: 0,
    floodRiskPremium: 0,
    earthquakeRiskPremium: 0,
    totalPremium: 0,
    annualPremium: 0,
  });

  /**
   * Calculate years covered between two dates
   * @param {string} startDate - Coverage start date
   * @param {string} endDate - Coverage end date
   * @returns {number} - Number of years covered
   */
  const calculateYearsCovered = (startDate, endDate) => {
    if (!startDate || !endDate) return 0;

    const start = new Date(startDate);
    const end = new Date(endDate);

    // Calculate the difference in years
    const years = end.getFullYear() - start.getFullYear();

    // Adjust for months and days
    if (
      end.getMonth() < start.getMonth() ||
      (end.getMonth() === start.getMonth() && end.getDate() < start.getDate())
    ) {
      return years - 1;
    }

    return years;
  };

  /**
   * Calculate premium for provided customer data
   * @param {Object} data Vehicle and coverage data
   * @returns {Object} Premium calculation details
   */
  const calculatePremium = (data = null) => {
    // Use provided data or reactive customer data
    const coverageData = data || customerData;

    // Reset if no data
    if (!coverageData) {
      resetPremiumDetails();
      return premiumDetails;
    }

    // Extract required values
    const price = parseFloat(coverageData.price);
    const type = parseInt(coverageData.type);
    const isBanjir = !!coverageData.is_risk_banjir;
    const isGempa = !!coverageData.is_risk_gempa;

    // Validate required inputs
    if (isNaN(price) || price <= 0 || isNaN(type)) {
      resetPremiumDetails();
      return premiumDetails;
    }

    // Calculate years covered
    const yearsCovered = Math.max(
      1,
      calculateYearsCovered(
        coverageData.start_date_coverage,
        coverageData.end_date_coverage
      )
    );

    // Base premium calculation for vehicle
    const vehiclePremium = price * COVERAGE_RATES[type] * yearsCovered;

    // Risk premiums calculation
    const floodRiskPremium = isBanjir
      ? price * RISK_RATES[type].banjir * yearsCovered
      : 0;

    const earthquakeRiskPremium = isGempa
      ? price * RISK_RATES[type].gempa * yearsCovered
      : 0;

    // Total premium
    const totalPremium =
      vehiclePremium + floodRiskPremium + earthquakeRiskPremium;

    // Annual premium (for yearly payment calculation)
    const annualPremium = totalPremium / yearsCovered;

    // Update premium details
    premiumDetails.yearsCovered = yearsCovered;
    premiumDetails.vehiclePremium = vehiclePremium;
    premiumDetails.floodRiskPremium = floodRiskPremium;
    premiumDetails.earthquakeRiskPremium = earthquakeRiskPremium;
    premiumDetails.totalPremium = totalPremium;
    premiumDetails.annualPremium = annualPremium;

    return premiumDetails;
  };

  /**
   * Reset premium calculation details
   */
  const resetPremiumDetails = () => {
    premiumDetails.yearsCovered = 0;
    premiumDetails.vehiclePremium = 0;
    premiumDetails.floodRiskPremium = 0;
    premiumDetails.earthquakeRiskPremium = 0;
    premiumDetails.totalPremium = 0;
    premiumDetails.annualPremium = 0;
  };

  /**
   * Format a number as Indonesian currency (IDR)
   * @param {number} amount - Amount to format
   * @returns {string} - Formatted currency string
   */
  const formatCurrency = (amount) => {
    return new Intl.NumberFormat("id-ID", {
      style: "currency",
      currency: "IDR",
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    }).format(amount);
  };

  /**
   * Get coverage type name from ID
   * @param {number} typeId - Coverage type ID
   * @param {Object} coverageTypes - Available coverage types
   * @returns {string} - Coverage type name
   */
  const getCoverageTypeName = (typeId, coverageTypes = {}) => {
    for (const [name, id] of Object.entries(coverageTypes)) {
      if (id == typeId) {
        return name;
      }
    }
    return typeId === 1
      ? "Comprehensive"
      : typeId === 2
      ? "Total Loss Only"
      : "Unknown";
  };

  // Computed properties for formatted values
  const formattedPremium = computed(() => ({
    vehiclePremium: formatCurrency(premiumDetails.vehiclePremium),
    floodRiskPremium: formatCurrency(premiumDetails.floodRiskPremium),
    earthquakeRiskPremium: formatCurrency(premiumDetails.earthquakeRiskPremium),
    totalPremium: formatCurrency(premiumDetails.totalPremium),
    annualPremium: formatCurrency(premiumDetails.annualPremium),
  }));

  // Set up automatic computation if enabled and reactive data is provided
  if (autoCompute && customerData) {
    // Watch for changes to relevant customer data properties
    watch(
      () => [
        customerData.price,
        customerData.type,
        customerData.start_date_coverage,
        customerData.end_date_coverage,
        customerData.is_risk_banjir,
        customerData.is_risk_gempa,
      ],
      () => {
        calculatePremium();
      },
      { immediate: true, deep: true }
    );
  }

  // Return the composable API
  return {
    // State
    premiumDetails,

    // Computed
    formattedPremium,

    // Actions
    calculatePremium,
    resetPremiumDetails,
    calculateYearsCovered,

    // Helpers
    formatCurrency,
    getCoverageTypeName,

    // Constants
    COVERAGE_RATES,
    RISK_RATES,
  };
}
