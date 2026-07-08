import { describe, it, expect } from 'vite-plus/test'
import { areStringsEqual } from '@/utils/validation'

describe('validation utils', () => {
  describe('areStringsEqual', () => {
    it('returns true when strings are equal', () => {
      expect(areStringsEqual('test', 'test')).toBe(true)
      expect(areStringsEqual('password123', 'password123')).toBe(true)
      expect(areStringsEqual('', '')).toBe(true)
    })

    it('returns false when strings are different', () => {
      expect(areStringsEqual('test', 'Test')).toBe(false)
      expect(areStringsEqual('password', 'password123')).toBe(false)
      expect(areStringsEqual('abc', 'def')).toBe(false)
    })

    it('is case sensitive', () => {
      expect(areStringsEqual('ABC', 'abc')).toBe(false)
      expect(areStringsEqual('Test', 'test')).toBe(false)
    })

    it('handles empty strings', () => {
      expect(areStringsEqual('', '')).toBe(true)
      expect(areStringsEqual('test', '')).toBe(false)
      expect(areStringsEqual('', 'test')).toBe(false)
    })

    it('handles special characters', () => {
      expect(areStringsEqual('test@123', 'test@123')).toBe(true)
      expect(areStringsEqual('a b c', 'a b c')).toBe(true)
      expect(areStringsEqual('test!', 'test?')).toBe(false)
    })
  })
})
