import { defineStore } from 'pinia'
import { ref } from 'vue'
import { eventHandler } from '../scripts/eventHandler'
import { createToaster } from "@meforma/vue-toaster";
import { HttpClient } from '../services/HttpClient';
const toaster = createToaster({
  position: "top",
});


export const useStore = defineStore('currentblock', {
  state: () => {
    return {
      id_prettyblocks: null,
      instance_id: null,
      code: null,
      subSelected: null,
      need_reload: true

    }
  }
})

export const usePrettyBlocksContext = defineStore('prettyBlocksContext', {
  state: () => ({
    blocks: [],
    blocksFormatted: [],
    zones: [],
    currentBlock: {
      id_prettyblocks: null,
      instance_id: null,
      code: null,
      subSelected: null,
      need_reload: true,
      states: []
    },
    currentZone: {
      name: 'displayHome',
      alias: '',
      priority: true,
      zoneToFocus: 'displayHome',
    },
    psContext: {
      id_lang: 1,
      id_shop: 1,
      shop_name: null,
      current_url: ajax_urls.startup_url + (ajax_urls.startup_url.includes('?prettyblocks=1') ? '' : '?prettyblocks=1'),
      href: ajax_urls.startup_url,
    },
    iframe: {
      domElement: ref(null),
      object: null,
      width: ref('w-full'),
      height: ref('h-full'),
      device: ref('desktop'),
      loader: ref(true),
      rightPanel: ref('default'), // should be defaut, extends or hide
      leftPanel: ref('default'), //  should be defaut, extends or hide
    },
    saveContext: ref('settings'),
    eventListeners: {},
  }),
  getters: {
    getDomElement(state) {
      return state.iframe.domElement
    },
    getZones(state) {
      return state.zones
    },
    getBlocks(state) {
      return state.blocks
    },
    getCurrentBlock(state) {
      return state.currentBlock
    },
    getCurrentZone(state) {
      return state.currentZone
    },
    getPsContext(state) {
      return state.psContext
    },
    getIframe(state) {
      return state.iframe
    }
  },
  actions: {
    changeIframeSize(width, height, device) {
      this.$patch((state) => {
        state.iframe.width = ref(width)
        state.iframe.height = ref(height)
        state.iframe.device = ref(device)
      })
    },
    initStates() {

      let context =  this.psContext;
      let current_zone = this.currentZone.name;
      // displayZoneName.value = current_zone;
      const params = {
        ajax: true,
        action: "GetStates",
        zone: current_zone,
        ctx_id_lang: context.id_lang,
        ctx_id_shop: context.id_shop,
        ajax_token: security_app.ajax_token,
      };

      HttpClient.get(ajax_urls.state, params)
        .then((data) => {
          let blocksFormatted = Object.entries(data.blocks).map(([key, value] = block) => {
            return value.formatted;
          });
          this.$patch((state) => {
            state.blocks =  data.blocks,
            state.blocksFormatted = blocksFormatted
          });
        })
        .catch((error) => console.error(error));
    },
    reloadZoneContent() {
      this.sendPrettyBlocksEvents('reloadZone', {zone: this.currentZone.name})
    },
    setIframe() {
      this.$patch((state) => {
        state.iframe.domElement = ref(document.getElementById('website-iframe'))
        this.listenIframe()
      })
    },
    displaySettingsPanel() {
      this.$patch((state) => {
        state.saveContext = ref('settings')
      })
    },
    updatePanelState(side, value) {
      if (!['left', 'right'].includes(side)) {
        console.error('Invalid side parameter. Must be "left" or "right".');
        return;
      }
      
      if (!['default', 'extends', 'hide'].includes(value)) {
        console.error('Invalid value parameter. Must be "default", "extends", or "hide".');
        return;
      }

      this.$patch((state) => {
        state.iframe[`${side}Panel`] = ref(value);
      });
    },
    displayMessage(message) {
      toaster.show(message)
    },
    displayError(message) {
      toaster.error(message, {
        duration: 5000,
        position: "top",
        type: "error",
      })
    },
    listenIframe() {
      window.addEventListener("message", eventHandler);
      this.iframe.domElement.addEventListener('load', (e) => {
        setTimeout(() => {
          this.sendPrettyBlocksEvents('initIframe')
          this.sendPrettyBlocksEvents('getContext')
          this.sendPrettyBlocksEvents('getZones')
          this.hideLoader()
          this.emit('iframeLoaded')
        },100)
      })
    },

    sendPrettyBlocksEvents(eventType, data = []) {
      let message = { type: eventType, data: data };
      this.iframe.domElement.contentWindow.postMessage(message, "*");
    },
    changeUrl(url) {
      this.$patch((state) => {
        state.psContext.current_url = this.updateFilteredURL(url)
      })
      // Update the current window URL with startup_url parameter
      this.pushUrl(url)
      this.showLoader()
      this.setIframe()
      this.emit('urlChanged', url)

    },
    showLoader() {
      this.$patch((state) => {
        state.iframe.loader = true
      })
    },
    hideLoader() {
      this.$patch((state) => {
        state.iframe.loader = false
      })
    },  
    pushUrl(url) {
      const currentUrl = new URL(window.location.href);
      
      // Remove 'id' and 'endpoint' parameters if they exist
      currentUrl.searchParams.delete('id');
      currentUrl.searchParams.delete('endpoint');
      
      currentUrl.searchParams.set('startup_url', this.updateFilteredURL(url));
      window.history.replaceState({}, '', currentUrl.toString());
    },
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
    },
    reloadIframe(currentSrc = false) {
      if (this.iframe.domElement) {
        let url = this.iframe.domElement.src
        if(currentSrc === false){
          currentSrc = url
        }
        this.iframe.domElement.src = '';
        setTimeout(() => {
          this.iframe.domElement.src = currentSrc;
        }, 100);
      }
    },
    getSubSelectedKey()
    {
        let key_formatted = 0
        this.subSelected = this.currentBlock.subSelected
        if(typeof this.subSelected !== 'undefined'){
            key_formatted = this.subSelected.split('-')[1]
        } else {
            let maxKey = 0
            if(this.states.length == 0){
                let keys = Object.keys(this.states).map(Number);
                maxKey = Math.max(...keys); 
            }
            maxKey = maxKey + 1
            key_formatted = this.id_prettyblocks + '-' + maxKey;
        }
        return key_formatted
    },
    async saveConfig(configState) {

      const params = {
          id_prettyblocks: this.currentBlock.id_prettyblocks,
          action: 'updateBlockConfig',
          state: JSON.stringify(configState),
          subSelected: this.subSelected,
          ajax: true,
          ctx_id_lang: this.psContext.id_lang,
          ctx_id_shop: this.psContext.id_shop,
          ajax_token: security_app.ajax_token
      }
      let data = await HttpClient.post(ajax_urls.state, params);
      return data
      
    },
    async updateSubSelectItem(state)
    {
        const params = {
            id_prettyblocks: this.currentBlock.id_prettyblocks,
            action: 'updateState',
            state: JSON.stringify(state.value),
            subSelected: this.getSubSelectedKey(),
            ajax: true, 
            ctx_id_lang: this.psContext.id_lang,
            ctx_id_shop: this.psContext.id_shop,
            ajax_token: security_app.ajax_token
        }
        let data = await HttpClient.post(ajax_urls.state, params);

        return data
    },
    updateCurrentZone(zone) {
      this.$patch((state) => {
        state.currentZone.name = zone.name
        state.currentZone.alias = zone.alias
        state.currentZone.priority = zone.priority
        state.currentZone.zoneToFocus = zone.name
      })
    },
    setZoneToFocus(zoneName) {
      this.$patch((state) => {
        state.currentZone.zoneToFocus = zoneName
      })
    },
    async getContext() {
      return new Promise((resolve) => {
        resolve({
          id_lang: this.psContext.id_lang,
          id_shop: this.psContext.id_shop,
          shop_name: this.psContext.shop_name,
          current_url: this.psContext.current_url,
          href: this.psContext.href,
        });
      });
    },
    emit(event, ...args) {
      if (this.eventListeners[event]) {
        this.eventListeners[event].forEach(callback => callback(...args));
      }
    },

    on(event, callback) {
      if (!this.eventListeners[event]) {
        this.eventListeners[event] = [];
      }
      this.eventListeners[event].push(callback);
    },

    emitSaveContext() {
      switch(this.saveContext) {
        case 'config':
          this.emit('saveConfig');
          break;
        case 'settings':
          this.emit('saveSettings');
          break;
        case 'subState':
          this.emit('saveSubState');
          break;
        default:
          console.warn('Unknown saveContext:', this.saveContext);
      }
    },

    updateSaveContext(newContext) {
      this.saveContext = ref(newContext);
      this.emitSaveContext();
    },
  }
})

export const useIframe = defineStore('iframe', {
  state: () => ({
    domElement: ref("test"),
    object: null,
  }),
  actions: {
    setDomElement() {
      this.$patch((state) => {
        state.domElement = ref(document.getElementById('website-iframe'))
      })
    },

  },
  getters: {
    getDomElement(state) {
      return state.domElement
    }
  }
})

export const useCurrentZone = defineStore('currentZone', {
  state: () => ({
    name: 'displayHome',
    alias: '',
    priority: false,
    zoneToFocus: '',
  }),
  actions: {
    updateZone(zone) {
      this.name = zone.name
      this.alias = zone.alias
      this.priority = zone.priority
      this.zoneToFocus = zone.name
    },
    setZoneToFocus(zoneName) {
      this.zoneToFocus = zoneName
    }
  }
})

export const contextShop = defineStore('contextStore', {
  state: () => {
    return {
      id_lang: 0,
      id_shop: 0,
      shop_name: null,
      current_url: null,
      href: null,
    }
  },
  actions: {
    async getContext() {
      return new Promise((resolve) => {
        resolve({
          id_lang: this.id_lang,
          id_shop: this.id_shop,
          shop_name: this.shop_name,
          current_url: this.current_url,
          href: this.href,
        });
      });
    },
  },
})


export const useStoredZones = defineStore('useStoredZones', {
  state: () => {
    return {
      zones: [],
    }
  },
  getters: {
    all(state) {
      return state.zones
    }
  }


})

export const storedBlocks = defineStore('storedBlocks', {
  state: () => {
    return {
      blocks: [],
    }
  },
  getters: {
    all(state) {
      return state.blocks
    }
  }


})