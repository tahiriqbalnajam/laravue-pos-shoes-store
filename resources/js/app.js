import 'core-js';
import Vue from 'vue';
import Cookies from 'js-cookie';
import ElementUI from 'element-ui';
import App from './views/App';
import store from './store';
import router from '@/router';
import i18n from './lang'; // Internationalization
import '@/icons'; // icon
import '@/permission'; // permission control
import VueHtmlToPaper from 'vue-html-to-paper';
import VueStaticData from 'vue-static-data';
import Vueshortkey from 'vue-shortkey';
import * as filters from './filters'; // global filters
const basePath = 'http://localhost:8080';
const options = {
  name: '_blank',
  specs: [
    'fullscreen=yes',
    'titlebar=yes',
    'scrollbars=yes',
  ],
  styles: [
    'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css',
    `${basePath}/print.css`,
  ],
};
Vue.use(VueHtmlToPaper, VueStaticData, options);
Vue.use(ElementUI, {
  size: Cookies.get('size') || 'medium', // set element-ui default size
  i18n: (key, value) => i18n.t(key, value),
});
Vue.use(Vueshortkey);
// register global utility filters.
Object.keys(filters).forEach(key => {
  Vue.filter(key, filters[key]);
});

Vue.config.productionTip = false;

new Vue({
  el: '#app',
  router,
  store,
  i18n,
  VueHtmlToPaper,
  render: h => h(App),
});
