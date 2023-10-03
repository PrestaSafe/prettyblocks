
let eventHandler = (event) => {
    if (event.data.type == 'initIframe') {
        // register block click
        document.querySelectorAll('div[data-block]').forEach((div) => {

            div.addEventListener('click', (el) => {
                let id_prettyblocks = el.target.closest('[data-id-prettyblocks]').getAttribute('data-id-prettyblocks')
                selectBlock(id_prettyblocks)
                event.source.postMessage({ type: 'loadStateConfig', data: id_prettyblocks }, '*');
            })
        })
    }
    // focus on zone in iframe
    if (event.data.type == 'focusOnZone') {
        let zone_name = event.data.data
        document.querySelectorAll('.border-dotted').forEach((div) => {
            div.classList.remove('border-dotted')
        })
        let el = document.querySelector('[data-prettyblocks-zone="' + zone_name + '"]')
        el.classList.add('border-dotted')
        el.scrollIntoView({
            alignToTop: true,
            behavior: 'smooth',
            block: 'center'
        })

    }
    // update HTML block
    if (event.data.type == 'updateHTMLBlock') {
        let id_prettyblocks = event.data.data.id_prettyblocks
        let data = event.data.data.html
        let domBlock = document.querySelector('[data-id-prettyblocks="' + id_prettyblocks + '"]')
        domBlock.innerHTML = data

        // @TODO run toolbar

    }

    if (event.data.type == 'scrollInIframe') {
        selectBlock(event.data.data)
    }
    if (event.data.type == 'getZones') {
        let els = document.querySelectorAll('[data-zone-name]')
        let zones = []

        els.forEach((el) => {
            let zone_name = el.getAttribute('data-zone-name')
            if (zones.indexOf(zone_name) == -1) {
                zones.push(zone_name)
            }
        })
        event.source.postMessage({ type: 'zones', data: zones }, '*');
        // unsubscribe()
    }


}
const selectBlock = (id_prettyblocks) => {
    
    let doc = document
    let el = doc.querySelector('[data-id-prettyblocks="' + id_prettyblocks + '"]')
    if (doc.body.contains(el)) {
        el.scrollIntoView({
            alignToTop: false,
            behavior: 'smooth',
            block: 'center'
        })
        let tr = doc.querySelectorAll('[data-block]')
        tr.forEach(bl => {
            bl.classList.remove('border-dotted')
        })
        el.classList.add('border-dotted')
    }
}

const unsubscribe = () => {
    window.removeEventListener("message", eventHandler, false);
}
window.addEventListener("message", eventHandler, false)
