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
      shop_name: null
    } 
  }
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