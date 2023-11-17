import { createApp } from 'vue'
import App from './App.vue'
import { createPinia } from 'pinia'
const pinia = createPinia()
import './index.css'
const app = createApp(App).use(pinia)
app.mount('#app')

window.vueInstance = app
