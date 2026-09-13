import { describe, expect, it } from 'vitest'
import { checkpointBody, checkpointSubject } from './checkpoint'

describe('checkpoint helpers', () => {
    const checkpoint = { kind: 'checkpoint' as const, content: '# Checkpoint: Verification\n\n- Done: matching.' }
    const plain = { kind: 'comment' as const, content: '# Checkpoint: not really' }

    it('reads the subject from the first line of a checkpoint', () => {
        expect(checkpointSubject(checkpoint)).toBe('Verification')
        expect(checkpointBody(checkpoint)).toBe('- Done: matching.')
    })

    it('leaves plain comments untouched', () => {
        expect(checkpointSubject(plain)).toBeNull()
        expect(checkpointBody(plain)).toBe(plain.content)
    })
})
