export function getZoneDetailsByDom(domElement) {
    return {
        name: domElement.getAttribute('data-zone-name'),
        alias: domElement.getAttribute('data-zone-alias') || '',
        priority: domElement.getAttribute('data-zone-priority') || false,
    }
}