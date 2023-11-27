import { ref } from 'vue'
import { HttpClient } from "../services/HttpClient";
import emitter from 'tiny-emitter/instance'

import { useStore, storedZones, contextShop, storedBlocks } from '../store/currentBlock'
import Block from './block'


import { createToaster } from "@meforma/vue-toaster";
const toaster = createToaster({
    position: 'top',
});
window.hasEventListener = false;
export default class Iframe {
    current_url = ref();
    id_lang = ref(0);
    id_shop = ref(0);
    loader = ref(false)
    events = ['dragenter', 'dragover', 'dragleave', 'drop']
    preventDefaults = (e) => {
        e.preventDefault()
    }

    constructor(current_url, id_lang, id_shop) {
        this.current_url.value = current_url
        this.id_lang.value = id_lang
        this.id_shop.value = id_shop
        this.loader.value = false
        this.constructEvent()
    }

    constructEvent() {
        // Définir l'eventHandler
        let eventHandler = async (event) => {
            if (event.data.type == 'zones') {
                let zones = event.data.data
                let piniazones = storedZones()
                piniazones.$patch({
                    zones: zones
                })
                emitter.emit('loadZones', zones)
            }
            if(event.data.type == 'iframeInit')
            {
                this.loader.value = false
            }
            if (event.data.type == 'loadStateConfig') {
                let id_prettyblocks = event.data.data
                emitter.emit('loadStateConfig', id_prettyblocks)

            }
            // test 
            if(event.data.type == 'setNewUrl')
            {  
                let context = event.data.params.context
                let custom_url = event.data.params.url
                emitter.emit('changeUrl', context, custom_url)
            }

            if (event.data.type == 'updateTitleComponent') {
                let params = event.data.data.params
                this.updateTitleComponent(JSON.parse(event.data.data.value), params.id_prettyblocks, params.field, params.index)

            }
            if(event.data.type == 'moveBlockToZone')
            {
                let id_prettyblocks = event.data.params.id_prettyblocks
                let zone_name = event.data.params.zone_name
                let context = contextShop()
                HttpClient.post(ajax_urls.api, {
                    action: 'moveBlockToZone',
                    id_prettyblocks: id_prettyblocks,
                    zone_name: zone_name,
                    ajax: true,
                    ajax_token: security_app.ajax_token,
                    ctx_id_lang: context.id_lang,
                    ctx_id_shop: context.id_shop,
                }).then((response) => {
                    if (response.success) {
                        toaster.show(response.message);
                        emitter.emit('initStates')
                    }
                })

            }
            if(event.data.type == 'focusBlock')
            {
                let id_prettyblocks = event.data.data.id_prettyblocks
                let zone_name = event.data.data.zone_name
                let piniaBlocks =  await storedBlocks().blocks
                
                let element = await piniaBlocks.find(b => {
                    return b.id_prettyblocks == id_prettyblocks
                });
                emitter.emit('selectZone', zone_name)

                emitter.emit('displayBlockConfig', element)
                emitter.emit('setSelectedElement', element.formatted.id)
            }

            if (event.data.type == 'setContext') {
                let iwindow = event.data.data.data
                let context = contextShop()
                await context.$patch({
                    id_lang: iwindow.id_lang,
                    id_shop: iwindow.id_shop,
                    shop_name: iwindow.shop_name,
                    current_url: iwindow.current_url,
                    href: iwindow.href,
                })
                this.id_lang.value = iwindow.id_lang
                this.id_shop.value = iwindow.id_shop
                this.loader.value = false
                emitter.emit('initStates')


            }

        }

        if (!window.hasEventListener) {
            window.addEventListener("message", eventHandler, false);
            window.hasEventListener = true;
        }
    }

    /**
     * 
     * @param {*} url 
     * For set URL in input
     */
    setUrl(url) {
        this.loader.value = true
        this.current_url.value = url
    }
    setIdLang(id_lang) {
        this.id_lang.value = id_lang
    }
    setIdShop(id_shop) {
        this.id_shop.value = id_shop
    }

    async reloadIframe() {
        this.loader.value = true
        let iframe = document.getElementById('website-iframe')
        iframe.src = this.updateFilteredURL(this.current_url.value)
        // this.loadIframe()
        // this.loader.value = false
    }
    updateFilteredURL(url) {
        let hashIndex = url.indexOf('#');
        if (hashIndex !== -1) {
            url = url.substring(0, hashIndex) + '?prettyblocks=1' + url.substring(hashIndex);
        } else if (!url.includes('?')) {
            url += '?prettyblocks=1';
        } else if (!url.includes('prettyblocks')) {
            url += '&prettyblocks=1';
        }
        return url;
    }

    /**
     * trigger popup with blocks choice on Click
     */
    registerClickPopup(el) {
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
    registerDragLeave(el) {
        el.target.classList.remove('border-danger')
        el.target.classList.add('border-dark')
    }

    registerClick(el) {
        let id_prettyblocks = el.getAttribute('data-id-prettyblocks')
        emitter.emit('loadStateConfig', id_prettyblocks)
    }

    async getZones(document) {
        let els = document.querySelectorAll('[data-zone-name]')
        let zones = []

        await els.forEach((el) => {
            let zone_name = el.getAttribute('data-zone-name')
            if (zones.indexOf(zone_name) == -1) {
                zones.push(zone_name)
            }
        })


        let piniazones = storedZones()
        piniazones.$patch({
            zones: zones
        })

        return zones
    }

    sendPrettyBlocksEvents(eventType, data = []) {
        let message = { type: eventType, data: data };
        let iframe = document.getElementById('website-iframe')
        iframe.contentWindow.postMessage(message, "*");
    }



    async loadIframe() {
        // iframe
        this.loader.value = true
        let iframe = await document.getElementById('website-iframe')

        if (iframe) {
            await iframe.addEventListener('load', (e) => {

                this.sendPrettyBlocksEvents('initIframe')
                this.sendPrettyBlocksEvents('getZones')
                emitter.off('stateUpdated')
                emitter.on('stateUpdated', (id_prettyblocks) => {
                    let currentBlock = useStore()
                    let html = this.getBlockRender(id_prettyblocks)
                    // update module in iFrame !
                    html.then((data) => {
                        this.sendPrettyBlocksEvents('updateHTMLBlock', { id_prettyblocks: id_prettyblocks, html: data })
                    })

                })

                // when iframe loaded, get blocks
                emitter.off('scrollInIframe')
                emitter.on('scrollInIframe', (id_prettyblocks) => {
                    this.sendPrettyBlocksEvents('scrollInIframe', id_prettyblocks)
                })


                emitter.off('focusOnZone')
                emitter.on('focusOnZone', (zone_name) => {
                    this.sendPrettyBlocksEvents('focusOnZone', zone_name)

                    emitter.emit('initStates')

                })

                // check if block is already selected
                let currentBlock = useStore()
                if (currentBlock.subSelected) {
                    emitter.emit('scrollInIframe', currentBlock.id_prettyblocks)
                }
                this.loadContext(e)

            }, false)
        }
    }

    /**
     * Updpate title component in Config field using Toolbar
     * @param {*} newValue 
     */
    async updateTitleComponent(newValue, id_block = null, field = null, index = null) {
        if (!id_block) {
            id_block = newValue.html.closest('[data-id-prettyblocks]').getAttribute('data-id-prettyblocks')
        }
        if (!field) {
            field = newValue.html.getAttribute('data-field')
        }
        if (!index) {
            index = null
        }
        // if(newValue.html.hasAttribute('data-index'))
        // {
        //     index = newValue.html.getAttribute('data-index')
        // }

        let element = await Block.loadById(id_block)
        // emitter.emit('displayBlockConfig', element)
        let context = contextShop()
        let data = {
            id_prettyblocks: id_block,
            element: newValue,
            ctx_id_lang: context.id_lang,
            ctx_id_shop: context.id_shop,
            field: field,
            ajax: true,
            index: index,
            action: 'updateTitleComponent',
            ajax_token: security_app.ajax_token
        }

        try {
            const response = await HttpClient.post(ajax_urls.api, data);
            toaster.show(response.message);
        } catch (error) {
            console.error(error);
        }



    }

    loadContext(e) {
        this.sendPrettyBlocksEvents('getContext')
    }

    destroy() {
        // 1. Remove global event listeners
        emitter.off('triggerLoadedEvents');
        emitter.off('stateUpdated');
        emitter.off('scrollInIframe');
        emitter.off('focusOnZone');
        // 3. Reset object properties
        this.current_url.value = null;
        this.id_lang.value = 0;
        this.id_shop.value = 0;
        this.loader.value = false;
    }



    async getBlockRender(id_prettyblocks) {
        let context = contextShop()
        let responseData = {}
        const params = {
            ajax: true,
            id_prettyblocks: id_prettyblocks,
            action: 'GetBlockRender',
            ctx_id_lang: context.id_lang,
            ctx_id_shop: context.id_shop,
            ajax_token: security_app.ajax_token
        }
        try {
            const data = await HttpClient.get(ajax_urls.block_url, params);
            responseData = data.html;
        } catch (error) {
            console.error(error);
        }

        return responseData;
    }

}