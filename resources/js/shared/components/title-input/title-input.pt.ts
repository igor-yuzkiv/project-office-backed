/** The title reads as a heading, not a field: no chrome until it is focused. */
export const TITLE_INPUT_PT = {
    root: {
        class: [
            '!type-title text-ink placeholder:text-ink-3 mt-1.5 mb-3 w-full !rounded-none !border-0 !bg-transparent !px-0 !py-1 !shadow-none',
            'focus:!shadow-[inset_0_-2px_0_var(--color-accent)]',
        ].join(' '),
    },
}
