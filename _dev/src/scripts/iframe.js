import { ref } from 'vue' 
import axios from 'axios'
import emitter from 'tiny-emitter/instance'
import {toolbar} from './toolbar';
import { useStore, storedZones, contextShop } from '../store/currentBlock'
import Block from './block'
export default class Iframe {
    current_url = ref();
    id_lang = ref(0);
    id_shop = ref(0);
    loader = ref(false)
    events = ['dragenter', 'dragover', 'dragleave', 'drop']
    preventDefaults = (e) => {
        e.preventDefault()
    }
    constructor(current_url, id_lang, id_shop)
    {
        this.current_url.value = current_url
        this.id_lang.value = id_lang
        this.id_shop.value = id_shop  
        this.loader.value = false
    }
    

    /**
     * When register on Element after Ajax
     */
    // async registerDrop (el) {
    // let currentBlock = useStore()

    //     axios.get(ajax_urls.block_url, {
    //             params: {
    //                 ajax: true,
    //                 block: currentBlock.code,
    //                 action: 'BlockRender',
    //             }
    //     })
    //     .then((response) => {

    //         let newNode = document.createElement('div')
    //         newNode.innerHTML = response.data.html + '<div class="blocks  border-dotted text-center w-100 p-5 mt-5">Zone de drop</div>'
    //         el.target.parentNode.replaceChild(newNode, el.target)
    //         newNode.addEventListener('click', (el) => {
    //             registerClickPopup(el)
    //         })
    //         newNode.addEventListener('drop', (el) => {
    //             registerDrop(el)
    //             registerDragEnter(el)
    //             registerDragLeave(el)
    //             setTimeout(() => {
    //                 emitter.emit('triggerLoadedEvents', el)
    //             }, 200)

    //         })
    //         setTimeout(() => {
    //             emitter.emit('triggerLoadedEvents', el)
    //         }, 200)
    //     })
    // }

    /**
     * 
     * @param {*} url 
     * For set URL in input
     */
    setUrl(url)
    {
        this.current_url.value = url
    }

    async reloadIframe() {
        this.loader.value = true
        let iframe = document.getElementById('website-iframe')
        iframe.src = this.current_url.value

        // setTimeout(() => {
        //     var x = iframe.contentWindow;
        //         x.location.reload(true)
        //     }, 200)    
        this.loadIframe()
        this.loader.value = false
    }

    /**
 * trigger popup with blocks choice on Click
 */
registerClickPopup (el) {
    let zone_name = el.target.getAttribute('data-zone-name')
    emitter.emit('toggleModal', zone_name)
}


/**
 * DragEnter Event
 */
registerDragEnter(el) {
    el.target.classList.remove('border-dark')
    el.target.classList.add('border-danger')
}

/**
 * DragLeave event
 */
registerDragLeave  (el) {
    el.target.classList.remove('border-danger')
    el.target.classList.add('border-dark')
}

registerClick (el) {
    let id_prettyblocks = el.getAttribute('data-id-prettyblocks')
    emitter.emit('loadStateConfig', id_prettyblocks)
}

async getZones(document) {
    let els = document.querySelectorAll('[data-zone-name]')
    let zones = []
   
    await els.forEach((el) => {
        let zone_name = el.getAttribute('data-zone-name')
        if(zones.indexOf(zone_name) == -1){
            zones.push(zone_name)
        }
    })


    let piniazones = storedZones()
    piniazones.$patch({
        zones: zones
    })

    return zones
}

async loadIframe () {
    // iframe
    this.loader.value = true
    let iframe = await document.getElementById('website-iframe')

    if (iframe) {
        await iframe.addEventListener('load', (e) => {
            let doc = e.target.contentWindow.document
            let jQuery = e.target.contentWindow.$
            let iwindow = e.target.contentWindow
            emitter.off('triggerLoadedEvents')
            emitter.on('triggerLoadedEvents', (dom) => {
                // trigger for init theme
                // e.target.contentWindow.dispatchEvent(new Event('load'));cz-homecategory order-4
                jQuery(doc).trigger("reloadEverything");
            })


            let body = doc.body
            this.events.forEach((eventName) => {
                doc.body.addEventListener(eventName, this.preventDefaults)
            })
            // getZones
            this.getZones(doc).then((zones) => {
                // emitter.off('loadZones')
                emitter.emit('loadZones', zones)
            })

            // detect new url
            iwindow.addEventListener('beforeunload', function(e) {
                // this.currentUrl.value = e.target.URL

                // currentUrl.value = iwindow.location.href
                // iframe.src = iwindow.location.href
            });
  

            emitter.off('stateUpdated')
            emitter.on('stateUpdated', (id_prettyblocks) => {
                let currentBlock = useStore()
                let html = this.getBlockRender(id_prettyblocks)
                // update module in iFrame !
                html.then((data) => {
                    let domBlock = body.querySelector('[data-id-prettyblocks="' + currentBlock.id_prettyblocks + '"]')
                    domBlock.innerHTML = data
                })
            })

            // when iframe loaded, get blocks
            emitter.off('scrollInIframe')
            emitter.on('scrollInIframe', (id_prettyblocks) => {
                let el = doc.querySelector('[data-id-prettyblocks="' + id_prettyblocks + '"]')
                if(doc.body.contains(el))
                {
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
            })

            // hover blocks 
            body.querySelectorAll('div[data-block]').forEach((div) => {
                div.addEventListener('click', (el) => {
                    this.registerClick(el.target)
                })
                // div.addEventListener('mouseover', (el) => {
                //     let block = el.target.closest('[data-block]')
                //     if(!block.classList.contains('border-dotted'))
                //     {
                //         block.classList.add('border-dotted')
                //     }
                // })

                //  div.addEventListener('mouseleave', (el) => {
                //     let block = el.target.closest('[data-block]')
                //     if(block.classList.contains('border-dotted'))
                //     {
                //         block.classList.remove('border-dotted')
                //     }
                // })
            })
            emitter.off('focusOnZone')
            emitter.on('focusOnZone', (zone_name) => {
                body.querySelectorAll('.border-dotted').forEach((div) => {
                    div.classList.remove('border-dotted')
                })
                let el = body.querySelector('[data-prettyblocks-zone="'+zone_name+'"]')
                el.classList.add('border-dotted')
                 el.scrollIntoView({
                    alignToTop: true,
                    behavior: 'smooth',
                    block: 'center'
                })

                emitter.emit('initStates')

            })
            body.querySelectorAll('main div.blocks').forEach((div) => {
                div.addEventListener('click', (el) => {
                    this.registerClickPopup(el)
                })
               
                // div.addEventListener('drop', (el) => {
                //     this.registerDrop(el)
                // }, false)
                div.addEventListener('dragenter', (el) => {
                    this.registerDragEnter(el)
                })
                div.addEventListener('dragleave', (el) => {
                    this.registerDragLeave(el)
                })

            })

            const tb = new toolbar( body.querySelectorAll('.ptb-title'), doc, iwindow);
            this.loadToolBar(tb)

            // check if block is already selected
            let currentBlock = useStore()
            if(currentBlock.subSelected)
            {
                emitter.emit('scrollInIframe', currentBlock.id_prettyblocks)
            }

            this.loadContext(e)
            
        }, false)
    }
}

loadToolBar(tb)
{
    tb.on('change', async (oldValue, newValue) => {
        this.updateTitleComponent(newValue)
    })
    tb.on('apply', async (oldValue, newValue) => {
        this.updateTitleComponent(newValue)
    })
}
/**
 * Updpate title component in Config field using Toolbar
 * @param {*} newValue 
 */
async  updateTitleComponent(newValue)
{
    let id_block = newValue.html.closest('[data-id-prettyblocks]').getAttribute('data-id-prettyblocks')
    let field = newValue.html.getAttribute('data-field')
    let element = await Block.loadById(id_block)
    emitter.emit('displayBlockConfig', element)
    let context = contextShop()
    let data = {
        id_prettyblocks: id_block,
        element: newValue,
        id_lang: context.id_lang,
        id_shop: context.id_shop,
        field: field,
        action: 'UpdateTitleComponent'
    }
    // fetch(this.urlUpdateTitle, {
}

loadContext(e)
{
    let iwindow = e.target.contentWindow
    let context = contextShop()
    context.$patch({
        id_lang: iwindow.prestashop.language.id,
        id_shop: iwindow.prestashop.modules.prettyblocks.id_shop,
        shop_name: iwindow.prestashop.modules.prettyblocks.shop_name,
        current_url: iwindow.prestashop.modules.prettyblocks.shop_current_url,
        href: iwindow.document.location.href
    })
    this.id_lang.value = iwindow.prestashop.language.id
    this.id_shop.value = iwindow.prestashop.modules.prettyblocks.id_shop
    emitter.emit('initStates')
    this.loader.value = false
}

destroy() {
    // 1. Supprimer les écouteurs d'événements globaux
    emitter.off('triggerLoadedEvents');
    emitter.off('stateUpdated');
    emitter.off('scrollInIframe');
    emitter.off('focusOnZone');
    // 3. Réinitialiser les propriétés de l'objet
    this.current_url.value = null;
    this.id_lang.value = 0;
    this.id_shop.value = 0;
    this.loader.value = false;
}



async getBlockRender (id_prettyblocks) {
    let context = contextShop()
    let responseData = {}
    await axios.get(ajax_urls.block_url, {
            params: {
                ajax: true,
                id_prettyblocks: id_prettyblocks,
                action: 'GetBlockRender',
                ctx_id_lang: context.id_lang,
                ctx_id_shop: context.id_shop,
                ajax_token: security_app.ajax_token
            }
        })
        .then((response) => {
            responseData = response.data.html
        })
    return responseData
}

   



}