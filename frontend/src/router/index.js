import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import ServiceDetailView from '../views/ServiceDetailView.vue'
import ProvidersView from '../views/ProvidersView.vue'
import ConsumersView from '../views/ConsumersView.vue'
import ApiTestView from '../views/ApiTestView.vue'

const routes = [
  { path: '/',            component: HomeView },
  { path: '/services/:name', component: ServiceDetailView },
  { path: '/providers',   component: ProvidersView },
  { path: '/consumers',   component: ConsumersView },
  { path: '/test',        component: ApiTestView },
]

export default createRouter({
  history: createWebHistory(),
  routes,
})
