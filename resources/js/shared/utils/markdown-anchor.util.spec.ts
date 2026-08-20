import { describe, expect, it } from 'vitest'
import {
    buildAnchor,
    buildBlockDescriptors,
    buildTextSnapshot,
    hashBlockText,
    normalizeBlockText,
    resolveAnchor,
    textSimilarity,
    type BlockDescriptor,
} from '@/shared/utils/markdown-anchor.util'

function descriptorsOf(blocks: Array<{ tag: string; line: number | null; text: string }>): BlockDescriptor[] {
    return buildBlockDescriptors(blocks)
}

describe('normalizeBlockText', () => {
    it('collapses line breaks and repeated spaces into single spaces', () => {
        expect(normalizeBlockText('  first\n\n  second   third \t fourth  ')).toBe('first second third fourth')
    })

    it('keeps emoji and Cyrillic intact', () => {
        expect(normalizeBlockText('  Привіт  🎉  світ ')).toBe('Привіт 🎉 світ')
    })

    it('treats different Unicode forms of the same text as equal', () => {
        expect(normalizeBlockText('éclair')).toBe(normalizeBlockText('éclair'))
    })
})

describe('hashBlockText', () => {
    it('gives the same hash to the same text', () => {
        expect(hashBlockText('A block of text.')).toBe(hashBlockText('A block of text.'))
    })

    it('ignores differences in whitespace only', () => {
        expect(hashBlockText('A block\nof   text.')).toBe(hashBlockText('A block of text.'))
    })

    it('gives different hashes to different texts', () => {
        expect(hashBlockText('First block.')).not.toBe(hashBlockText('Second block.'))
    })

    // Golden vectors: these values are written to the database and must survive any refactor
    // of the hash or of the normalization it runs first.
    it.each([
        ['The first paragraph of the annotated document.', 'b33bd1f7'],
        ['Привіт світ', '792f6b4b'],
        ['🎉 party', '45dbaf0e'],
        ['', '811c9dc5'],
    ])('hashes %j to %s', (text, expected) => {
        expect(hashBlockText(text)).toBe(expected)
    })

    it('always returns eight lowercase hex characters', () => {
        const samples = ['', 'a', 'Привіт світ', 'x'.repeat(5000), '🎉']

        for (const sample of samples) {
            expect(hashBlockText(sample)).toMatch(/^[0-9a-f]{8}$/)
        }
    })
})

describe('textSimilarity', () => {
    it('scores identical strings as one', () => {
        expect(textSimilarity('The same paragraph.', 'The same paragraph.')).toBe(1)
    })

    it('scores two empty strings as one', () => {
        expect(textSimilarity('', '   ')).toBe(1)
    })

    it('scores an empty string against a non-empty one as zero', () => {
        expect(textSimilarity('', 'Some text.')).toBe(0)
    })

    it('stays above the threshold when a typo is fixed in a long paragraph', () => {
        const before = 'The onboarding flow asks for the emial address before the password is set.'
        const after = 'The onboarding flow asks for the email address before the password is set.'

        expect(textSimilarity(before, after)).toBeGreaterThanOrEqual(0.5)
    })

    it('falls below the threshold for two different paragraphs of the same length', () => {
        const first = 'Deployment happens every Tuesday from the release branch.'
        const second = 'Support rotates weekly and is announced in the team channel.'

        expect(textSimilarity(first, second)).toBeLessThan(0.5)
    })
})

describe('buildBlockDescriptors', () => {
    it('counts ordinal within the same line and tag pair, not globally', () => {
        const descriptors = descriptorsOf([
            { tag: 'li', line: 4, text: 'first item' },
            { tag: 'p', line: 4, text: 'nested paragraph' },
            { tag: 'li', line: 4, text: 'second item' },
            { tag: 'li', line: 9, text: 'other list' },
        ])

        expect(descriptors.map((descriptor) => descriptor.ordinal)).toEqual([0, 0, 1, 0])
        expect(descriptors.map((descriptor) => descriptor.index)).toEqual([0, 1, 2, 3])
    })

    it('numbers blocks without a line of their own', () => {
        const descriptors = descriptorsOf([
            { tag: 'pre', line: null, text: 'const a = 1' },
            { tag: 'p', line: 3, text: 'between' },
            { tag: 'pre', line: null, text: 'const b = 2' },
        ])

        expect(descriptors.map((descriptor) => descriptor.ordinal)).toEqual([0, 0, 1])
    })
})

describe('resolveAnchor', () => {
    const paragraph = 'The onboarding flow asks for the email address before the password is set.'

    const documentBlocks = [
        { tag: 'h1', line: 0, text: 'Onboarding' },
        { tag: 'p', line: 2, text: paragraph },
        { tag: 'p', line: 4, text: 'Support rotates weekly and is announced in the team channel.' },
    ]

    const anchorOfSecondBlock = buildAnchor(descriptorsOf(documentBlocks)[1]!)
    const snapshot = buildTextSnapshot(paragraph)

    it('matches an unchanged block exactly', () => {
        const match = resolveAnchor(anchorOfSecondBlock, snapshot, descriptorsOf(documentBlocks))

        expect(match?.kind).toBe('exact')
        expect(match?.descriptor.text).toBe(paragraph)
    })

    it('matches by hash when a paragraph was inserted above and the line shifted', () => {
        const match = resolveAnchor(anchorOfSecondBlock, snapshot, [
            ...descriptorsOf([
                { tag: 'h1', line: 0, text: 'Onboarding' },
                { tag: 'p', line: 2, text: 'A new paragraph.' },
                { tag: 'p', line: 4, text: paragraph },
            ]),
        ])

        expect(match?.kind).toBe('hash')
        expect(match?.descriptor.text).toBe(paragraph)
    })

    it('picks the block whose index is closest when two blocks share the same text', () => {
        const match = resolveAnchor(
            anchorOfSecondBlock,
            snapshot,
            descriptorsOf([
                { tag: 'h1', line: 1, text: 'Onboarding' },
                { tag: 'p', line: 3, text: paragraph },
                { tag: 'p', line: 5, text: 'Support rotates weekly and is announced in the team channel.' },
                { tag: 'p', line: 7, text: paragraph },
            ])
        )

        expect(match?.kind).toBe('hash')
        expect(match?.descriptor.index).toBe(1)
    })

    it('prefers the smaller index when two matching blocks are equally far away', () => {
        const match = resolveAnchor(
            anchorOfSecondBlock,
            snapshot,
            descriptorsOf([
                { tag: 'p', line: 1, text: paragraph },
                { tag: 'h1', line: 3, text: 'Onboarding' },
                { tag: 'p', line: 5, text: paragraph },
            ])
        )

        expect(match?.descriptor.index).toBe(0)
    })

    it('matches by position when a typo was fixed in place', () => {
        const match = resolveAnchor(
            anchorOfSecondBlock,
            snapshot,
            descriptorsOf([
                { tag: 'h1', line: 0, text: 'Onboarding' },
                { tag: 'p', line: 2, text: paragraph.replace('email', 'e-mail') },
                { tag: 'p', line: 4, text: 'Support rotates weekly and is announced in the team channel.' },
            ])
        )

        expect(match?.kind).toBe('position')
    })

    it('matches by position when a typo was fixed in a block longer than the snapshot limit', () => {
        const longText = `${paragraph} ${'Filler sentence to push this block past the snapshot limit. '.repeat(20)}`
        const longBlocks = [
            { tag: 'h1', line: 0, text: 'Onboarding' },
            { tag: 'p', line: 2, text: longText },
        ]
        const longAnchor = buildAnchor(descriptorsOf(longBlocks)[1]!)

        const match = resolveAnchor(
            longAnchor,
            buildTextSnapshot(longText),
            descriptorsOf([
                { tag: 'h1', line: 0, text: 'Onboarding' },
                { tag: 'p', line: 2, text: longText.replace('email', 'e-mail') },
            ])
        )

        expect(match?.kind).toBe('position')
    })

    it('returns null when a different block took the anchored position', () => {
        const match = resolveAnchor(
            anchorOfSecondBlock,
            snapshot,
            descriptorsOf([
                { tag: 'h1', line: 0, text: 'Onboarding' },
                { tag: 'p', line: 2, text: 'Support rotates weekly and is announced in the team channel.' },
            ])
        )

        expect(match).toBeNull()
    })

    it('returns null when the block was deleted and nothing took its place', () => {
        const match = resolveAnchor(
            anchorOfSecondBlock,
            snapshot,
            descriptorsOf([{ tag: 'h1', line: 0, text: 'Onboarding' }])
        )

        expect(match).toBeNull()
    })

    it('returns null when the block kept its text but changed its tag', () => {
        const match = resolveAnchor(
            anchorOfSecondBlock,
            snapshot,
            descriptorsOf([
                { tag: 'h1', line: 0, text: 'Onboarding' },
                { tag: 'h2', line: 2, text: paragraph },
            ])
        )

        expect(match).toBeNull()
    })

    it('returns null for a code block whose hash no longer matches', () => {
        const codeBlocks = [{ tag: 'pre', line: null, text: 'const a = 1' }]
        const codeAnchor = buildAnchor(descriptorsOf(codeBlocks)[0]!)

        const match = resolveAnchor(
            codeAnchor,
            buildTextSnapshot('const a = 1'),
            descriptorsOf([{ tag: 'pre', line: null, text: 'const a = 2' }])
        )

        expect(match).toBeNull()
    })

    it('returns null without a snapshot when the hash no longer matches', () => {
        const match = resolveAnchor(
            anchorOfSecondBlock,
            null,
            descriptorsOf([
                { tag: 'h1', line: 0, text: 'Onboarding' },
                { tag: 'p', line: 2, text: 'A completely different paragraph now sits here.' },
            ])
        )

        expect(match).toBeNull()
    })

    it('does not match one empty block to another', () => {
        const emptyBlocks = [
            { tag: 'p', line: 1, text: '  ' },
            { tag: 'p', line: 5, text: '' },
        ]
        const emptyAnchor = buildAnchor(descriptorsOf(emptyBlocks)[0]!)

        const match = resolveAnchor(emptyAnchor, buildTextSnapshot('  '), descriptorsOf([emptyBlocks[1]!]))

        expect(match).toBeNull()
    })
})

describe('buildTextSnapshot', () => {
    it('truncates long text to 300 characters with an ellipsis', () => {
        const snapshot = buildTextSnapshot('x'.repeat(500))

        expect(snapshot).toHaveLength(301)
        expect(snapshot.endsWith('…')).toBe(true)
    })

    it('leaves short text as it is', () => {
        expect(buildTextSnapshot('  A short block.  ')).toBe('A short block.')
    })

    it('does not cut an emoji in half at the truncation point', () => {
        const snapshot = buildTextSnapshot(`${'x'.repeat(299)}🎉 tail`)

        expect(snapshot).toBe(`${'x'.repeat(299)}…`)
        expect(JSON.parse(JSON.stringify(snapshot))).toBe(snapshot)
    })

    it('returns an empty string for empty text', () => {
        expect(buildTextSnapshot('   \n  ')).toBe('')
    })
})
