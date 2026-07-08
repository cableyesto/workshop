import { fileURLToPath } from 'node:url'
import { mergeConfig, defineConfig, configDefaults } from 'vite-plus'
import viteConfig from './vite.config'

export default mergeConfig(
  viteConfig,
  defineConfig({
    test: {
      globals: true,
      environment: 'jsdom',
      exclude: [...configDefaults.exclude, 'e2e/**'],
      root: fileURLToPath(new URL('./', import.meta.url)),
      coverage: {
        provider: 'v8',
        include: ['src/**/*.{ts,tsx,vue}'],
        exclude: [
          'src/components/ui/**', // shadcn-vue
          // 'node_modules/**',
          // '**/*.d.ts',
          // '**/*.config.*',
          // '**/mockData/**',
        ],
      },
    },
  }),
)
