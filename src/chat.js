import { createApp } from 'vue'
import './style.css'
import App from './App.vue'


// const init = () => { //interactapiurl, uploadapiurl, stored_files
//     const app = createApp(App); //{interactapiurl: interactapiurl, uploadapiurl: uploadapiurl, storedfiles: stored_files}
//     app.mount("#plugin-spa-root");
//     app.config.errorHandler = (err) => {
//       console.log(err);
//     }
//   };
  
//   export { init };

createApp(App).mount('#plugin-spa-root')
