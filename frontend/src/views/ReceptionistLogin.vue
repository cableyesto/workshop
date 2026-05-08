<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { loginReceptionist } from '@/api/login'
import type { LoginReceptionistRequest } from '@/types/auth'
import LoginForm from '@/components/LoginForm.vue'

const router = useRouter()
const isLoading = ref(false)
const error = ref<string | null>(null)

async function handleLogin(credentials: { email: string; password: string; siret?: string }) {
  isLoading.value = true
  error.value = null

  try {
    // Ensure siret exists (it always will when userType="receptionist")
    if (!credentials.siret) {
      error.value = 'SIRET est requis'
      isLoading.value = false
      return
    }

    const receptionistCredentials: LoginReceptionistRequest = {
      siret: credentials.siret,
      email: credentials.email,
      password: credentials.password,
    }

    const { token } = await loginReceptionist(receptionistCredentials)

    // Store JWT in localStorage (refresh token is HttpOnly cookie)
    localStorage.setItem('jwt_token', token)

    // Redirect to receptionist dashboard
    await router.push('/receptionist/dashboard')
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
      <LoginForm user-type="receptionist" @submit="handleLogin" />

      <!-- Error message -->
      <p v-if="error" class="text-center text-sm text-red-600 2xl:text-lg">
        {{ error }}
      </p>

      <!-- Loading indicator -->
      <p v-if="isLoading" class="text-center text-sm text-gray-600 2xl:text-lg">Connexion en cours...</p>
    </div>
  </div>
</template>
