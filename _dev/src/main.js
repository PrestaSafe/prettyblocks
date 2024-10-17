import { createApp } from 'vue'
import App from './App.vue'
import { createPinia } from 'pinia'
import './index.css'
import { verifyConnectedEmployees } from './scripts/editorManager';

const app = createApp(App).use(createPinia())
app.mount('#app')

window.vueInstance = app
