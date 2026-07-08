import { describe, it, expect } from 'vite-plus/test'
import { formatDate } from '@/utils/date'

describe('date utils', () => {
  describe('formatDate', () => {
    it('formats ISO date to DD/MM/YYYY', () => {
      expect(formatDate('2024-03-15')).toBe('15/03/2024')
      expect(formatDate('2023-12-25')).toBe('25/12/2023')
      expect(formatDate('2020-01-01')).toBe('01/01/2020')
    })

    it('handles ISO datetime strings without timezone', () => {
      expect(formatDate('2024-03-15T10:30:00')).toBe('15/03/2024')
      expect(formatDate('2023-12-25T12:00:00')).toBe('25/12/2023')
    })

    it('pads single digit days and months with zero', () => {
      expect(formatDate('2024-01-05')).toBe('05/01/2024')
      expect(formatDate('2024-09-01')).toBe('01/09/2024')
      expect(formatDate('2024-03-09')).toBe('09/03/2024')
    })

    it('handles different years correctly', () => {
      expect(formatDate('1990-05-15')).toBe('15/05/1990')
      expect(formatDate('2000-06-20')).toBe('20/06/2000')
      expect(formatDate('2025-12-31')).toBe('31/12/2025')
    })

    it('handles edge case dates', () => {
      // First day of year
      expect(formatDate('2024-01-01')).toBe('01/01/2024')

      // Last day of year
      expect(formatDate('2024-12-31')).toBe('31/12/2024')

      // Leap year date
      expect(formatDate('2024-02-29')).toBe('29/02/2024')
    })

    it('handles months correctly (0-indexed to 1-indexed)', () => {
      expect(formatDate('2024-01-15')).toBe('15/01/2024') // January
      expect(formatDate('2024-06-15')).toBe('15/06/2024') // June
      expect(formatDate('2024-12-15')).toBe('15/12/2024') // December
    })
  })
})
