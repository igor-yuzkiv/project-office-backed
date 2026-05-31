export function getInitials(name: string, limit = 2): string {
    return name
        .split(' ')
        .slice(0, limit)
        .map((part) => part[0]?.toUpperCase() ?? '')
        .join('')
}
