<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { computed } from 'vue'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field'
import { Input } from '@/components/ui/input'

const props = defineProps<{
  userType: 'owner' | 'receptionist'
  class?: HTMLAttributes['class']
}>()

const title = computed(() =>
  props.userType === 'owner' ? 'Connexion Propriétaire' : 'Connexion Réceptionniste'
)

const description = computed(() =>
  props.userType === 'owner'
    ? 'Entrez vos identifiants pour accéder à votre compte'
    : 'Entrez le SIRET du garage et vos identifiants'
)
</script>

<template>
  <div :class="cn('flex flex-col gap-6', props.class)">
    <Card>
      <CardHeader>
        <CardTitle class="text-[#3e8eab]">{{ title }}</CardTitle>
        <CardDescription>{{ description }}</CardDescription>
      </CardHeader>
      <CardContent>
        <form>
          <FieldGroup>
            <!-- SIRET field: Only for receptionist -->
            <Field v-if="userType === 'receptionist'">
              <FieldLabel for="siret">SIRET</FieldLabel>
              <Input
                id="siret"
                type="text"
                placeholder="12345678901234"
                maxlength="14"
                class="focus-visible:border-cyan-900/60 focus-visible:ring-cyan-900/30 selection:bg-cyan-900"
                required
              />
            </Field>

            <!-- Email field: Both -->
            <Field>
              <FieldLabel for="email">Email</FieldLabel>
              <Input
                id="email"
                type="email"
                placeholder="votre@email.com"
                class="focus-visible:border-cyan-900/60 focus-visible:ring-cyan-900/30 selection:bg-cyan-900"
                required
              />
            </Field>

            <!-- Password field: Both -->
            <Field>
              <FieldLabel for="password">Mot de passe</FieldLabel>
              <Input
                id="password"
                type="password"
                class="focus-visible:border-cyan-900/60 focus-visible:ring-cyan-900/30 selection:bg-cyan-900"
                required
              />
            </Field>

            <!-- Submit button -->
            <Field>
              <Button type="submit" class="w-full bg-cyan-800 hover:bg-cyan-900">
                Se connecter
              </Button>
            </Field>
          </FieldGroup>
        </form>
      </CardContent>
    </Card>
  </div>
</template>
