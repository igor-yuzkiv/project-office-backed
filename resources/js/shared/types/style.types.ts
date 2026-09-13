export type HexColor = `#${string}`

export type ComponentSize = 'xsmall' | 'small' | 'medium' | 'large' | 'xlarge'

/** Text and background of a status pill, for one colour scheme. */
export type StatusColors = { fg: HexColor; bg: HexColor }

/** The pill colours per scheme; the wrapper picks the pair for the active theme. */
export type ThemedStatusColors = { light: StatusColors; dark: StatusColors }
