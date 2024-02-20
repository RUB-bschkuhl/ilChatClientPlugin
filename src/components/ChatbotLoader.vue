
<template>
  <div id="chatbot-loader" :class="active ? 'active' : ''">
    <div class="circle"></div>
  </div>
</template>


<script>

export default {
  name: 'ChatbotLoader',
  props: {
    active: Boolean
  },
  mounted() {
  },
};
</script>

<style scoped>
#chatbot-loader {
  display: none;
  position: relative;
}

#chatbot-loader.active {
  display: flex;
  justify-content: center;
  width: 100%;
  height: 3rem;
  top: 0;
  left: 0;
}

#chatbot-loader.active .circle {
  width: 2rem;
  height: 2rem;
  top: 0;
  border-radius: 50%;
  border: solid 4px #17365c;
  border-right-color: transparent;
  border-bottom-color: transparent;
  animation-name: spin2;
  animation-duration: 900ms;
  animation-iteration-count: infinite;
  animation-timing-function: linear;
  animation-delay: 0s;
  animation-direction: normal;
  animation-fill-mode: none;
  animation-play-state: running;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }

  100% {
    transform: rotate(360deg);
  }
}
</style>