import '@/app/style/base.css'

import { createApp } from 'vue'
import App from '@/app/App.vue'
import { registerPlugins } from '@/app/plugins'
import router from '@/app/router'
import { createPinia } from 'pinia'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)
registerPlugins(app)

app.mount('#app')
