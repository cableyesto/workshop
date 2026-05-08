<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { loginOwner } from '@/api/login'
import { setToken } from '@/utils/auth'
import type { LoginOwnerRequest } from '@/types/auth'
import LoginForm from '@/components/LoginForm.vue'

const router = useRouter()
const isLoading = ref(false)
const error = ref<string | null>(null)

async function handleLogin(credentials: LoginOwnerRequest) {
  isLoading.value = true
  error.value = null

  try {
    const { token } = await loginOwner(credentials)

    // Store JWT in localStorage
    setToken(token)

    // Redirect to owner dashboard
    await router.push('/owner/dashboard')
  } catch (err) {
    error.value = err instanceof Error ? err.message : 'Échec de connexion'
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-slate-50">
    <div class="w-full max-w-md space-y-4 2xl:max-w-2xl 2xl:space-y-8 2xl:py-12">
      <LoginForm user-type="owner" @submit="handleLogin" />

      <!-- Error message -->
      <p v-if="error" class="text-center text-sm text-red-600 2xl:text-lg">
        {{ error }}
      </p>

      <!-- Loading indicator -->
      <p v-if="isLoading" class="text-center text-sm text-gray-600 2xl:text-lg">
        Connexion en cours...
      </p>
    </div>
  </div>
</template>
