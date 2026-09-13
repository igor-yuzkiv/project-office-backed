/** Time-of-day greeting for the Home header: morning from 5, afternoon from 12, evening from 17. */
export function greetingFor(date: Date, name: string | null | undefined): string {
    const hour = date.getHours()
    const period = hour < 5 || hour >= 17 ? 'evening' : hour < 12 ? 'morning' : 'afternoon'
    const firstName = name?.trim().split(/\s+/)[0]
    return firstName ? `Good ${period}, ${firstName}` : `Good ${period}`
}
