import { ref } from 'vue'

export function trans (key, returnKey = true) {
    const trans = ref(trans_app)
    
    if(trans.value.hasOwnProperty(key))
    {
        return trans.value[key]
    }
    if(returnKey)
    {
        return key
    }
    return ''
}