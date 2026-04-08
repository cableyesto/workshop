<script setup lang="ts">
import { PiniaColadaDevtools } from '@pinia/colada-devtools'
import { ref } from 'vue'
import type { Ref } from 'vue'
import { Button } from '@/components/ui/button'

const year: Ref<string | number> = ref('2020')

year.value = 2020
</script>

<template>
  <h1 class="text-3xl font-bold underline">You did it!</h1>
  <p>
    Visit
    <a
      class="text-blue-500 underline decoration-blue-500"
      href="https://vuejs.org/"
      target="_blank"
      rel="noopener"
      >vuejs.org</a
    >
    to read the documentation
  </p>
  <Button>Click</Button>
  <Button>X</Button>
  <PiniaColadaDevtools />
</template>

<style scoped></style>
