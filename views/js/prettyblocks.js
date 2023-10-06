import { toolbar } from '../../_dev/src/scripts/toolbar'
window.hasEventListener = false;
const unsubscribe = () => {
    window.removeEventListener("message", eventHandler, false);
}
let eventHandler = (event) => {
    if(event.data.type == 'getContext')
    {
        let context = {
            id_lang: prestashop.language.id,
            id_shop: prestashop.modules.prettyblocks.id_shop,
            shop_name: prestashop.modules.prettyblocks.shop_name,
            current_url: prestashop.modules.prettyblocks.shop_current_url,
            href: window.location.href
        }
        return event.source.postMessage({ type: 'setContext', 
            data: { data: context } }, '*'); 
    }
    if (event.data.type == 'initIframe') {
        // register block click
        document.querySelectorAll('div[data-block]').forEach((div) => {

            div.addEventListener('click', (el) => {
                let id_prettyblocks = el.target.closest('[data-id-prettyblocks]').getAttribute('data-id-prettyblocks')
                selectBlock(id_prettyblocks)
                event.source.postMessage({ type: 'loadStateConfig', data: id_prettyblocks }, '*');
            })
        })
        return loadToolBar(event)
        
    }
    // focus on zone in iframe
    if (event.data.type == 'focusOnZone') {
        let zone_name = event.data.data
        document.querySelectorAll('.border-dotted').forEach((div) => {
            div.classList.remove('border-dotted')
        })
        let el = document.querySelector('[data-prettyblocks-zone="' + zone_name + '"]')
        el.classList.add('border-dotted')
        return el.scrollIntoView({
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
        return loadToolBar(event)
    }

    if (event.data.type == 'scrollInIframe') {
        return selectBlock(event.data.data)
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
        return event.source.postMessage({ type: 'zones', data: zones }, '*');
    }
    unsubscribe()

    
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
const loadToolBar = (event) => {
    const tb = new toolbar( document.querySelectorAll('.ptb-title'), document, window);
    tb.on('change', async (oldValue, newValue) => {
        let params = {
            id_prettyblocks: oldValue.html.closest('[data-id-prettyblocks]').getAttribute('data-id-prettyblocks'),
            field: oldValue.html.getAttribute('data-field'),
            index: oldValue.html.hasAttribute('data-index') ? oldValue.html.getAttribute('data-index') : null
        }
        event.source.postMessage({ type: 'updateTitleComponent', 
            data: { params: params, value: JSON.stringify(newValue) } }, '*');
    })
}

document.addEventListener('DOMContentLoaded', (event) => {
    if (!window.hasEventListener) {
        console.log('subscribe')
        window.addEventListener("message", eventHandler, false)
        window.hasEventListener = true;
    }
});
unsubscribe();