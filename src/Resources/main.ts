import { createApp, type Component } from 'vue'
import './style.css'

const components: Record<string, Component> = {}
const modules = import.meta.glob('./components/*.vue', { eager: true }) as Record<string, { default: Component }>
for (const path in modules) {
    const match = path.match(/\.\/components\/(.*)\.vue$/)
    if (match) {
        const componentName = match[1]
        components[componentName] = modules[path].default
    }
}

let el = document.getElementById('app');

if (!el) {
    throw new Error('No element with id "app" found in the DOM');
}

createApp({
    template: el.innerHTML,
    components
}).mount(el)
