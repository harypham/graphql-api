import Vue from 'vue/dist/vue.js'
import VueApollo from 'vue-apollo';
import { apollo } from './apollo';

Vue.use(VueApollo);
Vue.component('user-update-component', require('./components/UpdateUserComponent').default);


const app = new Vue({
    el: '#app',
    apolloProvider: apollo,
});
