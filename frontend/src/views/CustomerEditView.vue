<script setup>
import { useRoute } from "vue-router";
import CustomerForm from "@/components/CustomerForm.vue";

const route = useRoute();
const customerId = route.params.id;
</script>

<template>
  <div class="container mx-auto py-8 px-4">
    <CustomerForm :customer-id="customerId" />
  </div>
</template>