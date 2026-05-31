import type { App } from 'vue'
// import laravelEchoPlugin from './laravel-echo.plugin'
import primeVuePlugin from './prime-vue.plugin'
import vueQueryPlugin from './vue-query.plugin'

export function registerPlugins(app: App) {
    // laravelEchoPlugin()
    primeVuePlugin(app)
    vueQueryPlugin(app)
}
