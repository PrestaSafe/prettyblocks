import { createApp } from 'vue'
import App from './App.vue'
import { createPinia } from 'pinia'
const pinia = createPinia()
// global css
import './index.css'


createApp(App).use(pinia).mount('#app')