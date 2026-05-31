export type LaravelValidationErrors = Record<string, string[]>

export type LaravelValidationErrorResponse = {
    message: string
    errors: LaravelValidationErrors
}
