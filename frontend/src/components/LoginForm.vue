<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { computed } from 'vue'
import { toTypedSchema } from '@vee-validate/zod'
import { useForm, Field as VeeField } from 'vee-validate'
import { z } from 'zod'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Field, FieldGroup, FieldLabel, FieldError } from '@/components/ui/field'
import { Input } from '@/components/ui/input'

const props = defineProps<{
  userType: 'owner' | 'receptionist'
  class?: HTMLAttributes['class']
}>()

const emit = defineEmits<{
  submit: [credentials: { email: string; password: string; siret?: string }]
}>()

const title = computed(() =>
  props.userType === 'owner' ? 'Connexion Propriétaire' : 'Connexion Réceptionniste',
)

const description = computed(() =>
  props.userType === 'owner'
    ? 'Entrez vos identifiants pour accéder à votre compte'
    : 'Entrez le SIRET du garage et vos identifiants',
)

const formSchema = toTypedSchema(
  z.object({
    siret: z
      .string()
      .optional()
      .refine(
        (val) => (props.userType === 'owner' ? true : val?.length === 14),
        'SIRET doit contenir 14 caractères',
      ),
    email: z.string().email('Adresse email invalide').min(1, 'Email requis'),
    password: z
      .string()
      .min(1, 'Mot de passe requis')
      .max(50, 'Le mot de passe ne peut pas dépasser 50 caractères'),
  }),
)

const { handleSubmit } = useForm({
  validationSchema: formSchema,
  initialValues: {
    siret: '',
    email: '',
    password: '',
  },
})

const onSubmit = handleSubmit((values) => {
  const credentials: { email: string; password: string; siret?: string } = {
    email: values.email,
    password: values.password,
  }

  if (props.userType === 'receptionist') {
    credentials.siret = values.siret
  }

  emit('submit', credentials)
})
</script>

<template>
  <div :class="cn('flex flex-col gap-6 2xl:gap-8', props.class)">
    <Card class="bg-slate-200 2xl:py-8">
      <CardHeader class="2xl:space-y-4 2xl:pb-8 2xl:px-10">
        <CardTitle class="text-[#3e8eab] text-lg 2xl:text-4xl">{{ title }}</CardTitle>
        <CardDescription class="2xl:text-xl 2xl:pt-4">{{ description }}</CardDescription>
      </CardHeader>
      <CardContent class="2xl:px-10 2xl:pb-10">
        <form @submit="onSubmit">
          <FieldGroup>
            <!-- SIRET field: Only for receptionist -->
            <VeeField v-if="userType === 'receptionist'" v-slot="{ field, errors }" name="siret">
              <Field :data-invalid="!!errors.length">
                <FieldLabel for="siret" class="2xl:text-xl">SIRET</FieldLabel>
                <Input
                  id="siret"
                  v-bind="field"
                  type="text"
                  placeholder="12345678901234"
                  maxlength="14"
                  class="bg-slate-50 focus-visible:border-cyan-900/60 focus-visible:ring-cyan-900/30 selection:bg-cyan-900 2xl:h-12 2xl:text-lg"
                  :aria-invalid="!!errors.length"
                />
                <FieldError v-if="errors.length" :errors="errors" />
              </Field>
            </VeeField>

            <!-- Email field: Both -->
            <VeeField v-slot="{ field, errors }" name="email">
              <Field :data-invalid="!!errors.length">
                <FieldLabel for="email" class="2xl:text-xl">Email</FieldLabel>
                <Input
                  id="email"
                  v-bind="field"
                  type="email"
                  placeholder="votre@email.com"
                  class="bg-slate-50 focus-visible:border-cyan-900/60 focus-visible:ring-cyan-900/30 selection:bg-cyan-900 2xl:h-12 2xl:text-lg"
                  :aria-invalid="!!errors.length"
                />
                <FieldError v-if="errors.length" :errors="errors" />
              </Field>
            </VeeField>

            <!-- Password field: Both -->
            <VeeField v-slot="{ field, errors }" name="password">
              <Field :data-invalid="!!errors.length" class="2xl:pb-6">
                <FieldLabel for="password" class="2xl:text-xl">Mot de passe</FieldLabel>
                <Input
                  id="password"
                  v-bind="field"
                  type="password"
                  class="bg-slate-50 focus-visible:border-cyan-900/60 focus-visible:ring-cyan-900/30 selection:bg-cyan-900 2xl:h-12 2xl:text-lg"
                  :aria-invalid="!!errors.length"
                />
                <FieldError v-if="errors.length" :errors="errors" />
              </Field>
            </VeeField>

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
