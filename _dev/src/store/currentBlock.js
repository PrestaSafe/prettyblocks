import { defineStore } from 'pinia'

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

export const currentZone = defineStore('currentZone', {
  state: () => {
    return {
      name: 'displayHome',
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
      // Ici, vous pouvez effectuer des opérations asynchrones
      // comme la récupération des données de l'API ou d'autres tâches asynchrones

      // Retourner les données sous forme de promesse
      return new Promise((resolve) => {
        resolve({
          id_lang: this.id_lang,
          id_shop: this.id_shop,
          shop_name: this.shop_name,
        });
      });
    },
  },
})


export const storedZones = defineStore('storedZones', {
  state: () => {
    return {
      zones: [],
    }
  },
  getters: {
   all(state){  return state.zones }
  }


})

export const storedBlocks = defineStore('storedBlocks', {
  state: () => {
    return {
      blocks: [],
    }
  },
  getters: {
   all(state){  return state.blocks }
  }


})