<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { ref, computed } from 'vue'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field'
import { Input } from '@/components/ui/input'

const props = defineProps<{
  userType: 'owner' | 'receptionist'
  class?: HTMLAttributes['class']
}>()

const emit = defineEmits<{
  submit: [credentials: { email: string; password: string; siret?: string }]
}>()

const email = ref('')
const password = ref('')
const siret = ref('')

const title = computed(() =>
  props.userType === 'owner' ? 'Connexion Propriétaire' : 'Connexion Réceptionniste',
)

const description = computed(() =>
  props.userType === 'owner'
    ? 'Entrez vos identifiants pour accéder à votre compte'
    : 'Entrez le SIRET du garage et vos identifiants',
)

function handleSubmit() {
  const credentials: { email: string; password: string; siret?: string } = {
    email: email.value,
    password: password.value,
  }

  if (props.userType === 'receptionist') {
    credentials.siret = siret.value
  }

  emit('submit', credentials)
}
</script>

<template>
  <div :class="cn('flex flex-col gap-6 2xl:gap-8', props.class)">
    <Card class="bg-slate-200 2xl:py-8">
      <CardHeader class="2xl:space-y-4 2xl:pb-8 2xl:px-10">
        <CardTitle class="text-[#3e8eab] text-lg 2xl:text-4xl">{{ title }}</CardTitle>
        <CardDescription class="2xl:text-xl 2xl:pt-4">{{ description }}</CardDescription>
      </CardHeader>
      <CardContent class="2xl:px-10 2xl:pb-10">
        <form @submit.prevent="handleSubmit">
          <FieldGroup>
            <!-- SIRET field: Only for receptionist -->
            <Field v-if="userType === 'receptionist'">
              <FieldLabel for="siret" class="2xl:text-xl">SIRET</FieldLabel>
              <Input
                id="siret"
                v-model="siret"
                type="text"
                placeholder="12345678901234"
                maxlength="14"
                class="bg-slate-50 focus-visible:border-cyan-900/60 focus-visible:ring-cyan-900/30 selection:bg-cyan-900 2xl:h-12 2xl:text-lg"
                required
              />
            </Field>

            <!-- Email field: Both -->
            <Field>
              <FieldLabel for="email" class="2xl:text-xl">Email</FieldLabel>
              <Input
                id="email"
                v-model="email"
                type="email"
                placeholder="votre@email.com"
                class="bg-slate-50 focus-visible:border-cyan-900/60 focus-visible:ring-cyan-900/30 selection:bg-cyan-900 2xl:h-12 2xl:text-lg"
                required
              />
            </Field>

            <!-- Password field: Both -->
            <Field class="2xl:pb-6">
              <FieldLabel for="password" class="2xl:text-xl">Mot de passe</FieldLabel>
              <Input
                id="password"
                v-model="password"
                type="password"
                class="bg-slate-50 focus-visible:border-cyan-900/60 focus-visible:ring-cyan-900/30 selection:bg-cyan-900 2xl:h-12 2xl:text-lg"
                required
              />
            </Field>

            <!-- Submit button -->
            <Field>
              <Button
                type="submit"
                class="w-full bg-cyan-800 hover:bg-cyan-900 2xl:h-14 2xl:text-xl"
              >
                Se connecter
              </Button>
            </Field>
          </FieldGroup>
        </form>
      </CardContent>
    </Card>
  </div>
</template>
