import { ref } from 'vue'

export function trans (key) {
    const trans = ref(trans_app)
    
    if(trans.value.hasOwnProperty(key))
    {
        return trans.value[key]
    }
    return ''
}