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
        moveBlockToZone(event)
        
        
        // register block click
        document.querySelectorAll('div[data-block]').forEach((div) => {

            div.addEventListener('click', (el) => {
                let id_prettyblocks = el.target.closest('[data-id-prettyblocks]').getAttribute('data-id-prettyblocks')
                selectBlock(id_prettyblocks, event)
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
        return focusBlock(event.data.data)
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
/**
 * Select block in pretty block interface
 * @param {*} id_prettyblocks 
 * @param {*} event 
 * @returns 
 */
const selectBlock = (id_prettyblocks, event) => {
        let el = focusBlock(id_prettyblocks)
        let zone_name = el.closest('[data-prettyblocks-zone]').getAttribute('data-prettyblocks-zone')
        let params = {
            id_prettyblocks: id_prettyblocks,
            zone_name: zone_name
        }
        return event.source.postMessage({ type: 'focusBlock', data: params }, '*');

}
const focusBlock = (id_prettyblocks) => {
    let doc = document
    let el = doc.querySelector('[data-id-prettyblocks="' + id_prettyblocks + '"]')
    console.log('el BLOCK ', el)
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
        console.log('el block ', el)
        el.classList.add('border-dotted')
        return el
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

const moveBlockToZone = (event) => {
    let blockDragged = null;
    const blocks = document.querySelectorAll('[data-block]');
    const zones = document.querySelectorAll('[data-prettyblocks-zone]');

    blocks.forEach(block => {
        block.setAttribute('draggable', true);
        block.addEventListener('dragstart', () => {
            blockDragged = block;
            zones.forEach(zone => {
                zone.classList.add('ondrag');
            });
        });

        block.addEventListener('dragend', () => {
            
            zones.forEach(zone => {
                zone.classList.remove('ondrag');
            });
            blockDragged = null;
        });

       


    });

    zones.forEach(zone => {
        zone.addEventListener('dragover', function (e) {
            e.preventDefault();
        });

        zone.addEventListener('dragenter', function (e) {
            e.preventDefault();
        });
        zone.addEventListener('drop', function (e) {
            let zone_name = zone.getAttribute('data-prettyblocks-zone')
            let id_prettyblocks = blockDragged.getAttribute('data-id-prettyblocks')
            let params = {
                id_prettyblocks: id_prettyblocks,
                zone_name: zone_name
            }
            event.source.postMessage({ type: 'moveBlockToZone', params: params }, '*');
            this.appendChild(blockDragged);
        });
    });
}

document.addEventListener('DOMContentLoaded', (event) => {
    if (!window.hasEventListener) {
        // console.log('subscribe')
        window.addEventListener("message", eventHandler, false)
        window.hasEventListener = true;
    }
});
unsubscribe();