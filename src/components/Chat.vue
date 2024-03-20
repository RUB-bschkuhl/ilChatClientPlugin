<template>
  <div ref="chatblockparent" id="app">
    <div v-if="this.detached" class="row d-flex justify-content-center">
      <button :class="this.detached ? 'dock-btn' : ''" class="detach-btn btn-primary" type="button"
        @click="this.toggleDetach()">⏏</button>
    </div>
    <div class="chatblockwrapper" id="movable-chat" :class="this.detached ? 'chatbox-chat-container-detached' : ''">
      <div class="chatbox-chat-container">
        <loader :active="this.uploading"></loader>
        <!-- <div class="detach-btn" @click="this.toggleDetach()"></div> -->
        <ul class="nav nav-tabs" id="chat-tabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button :class="this.activetab == 'chat' ? 'nav-link active' : 'nav-link'" id="chat-tab" type="button"
              @click="this.activetab = 'chat'" role="tab">Chat</button>
          </li>
          <li class="nav-item" role="presentation">
            <button :class="this.activetab == 'files' ? 'nav-link active' : 'nav-link'" id="files-tab" type="button"
              role="tab" @click="this.activetab = 'files'">File Upload</button>
          </li>
          <li class="nav-item" v-if="!pageInEdit">
            <button class="nav-link detach-btn" id="detach-tab" type="button" role="tab" @click="this.toggleDetach()">⏏
            </button>
          </li>

        </ul>
        <div :class="this.activetab == 'chat' ? 'chat-container active' : 'chat-container'">
          <div class="info-box" v-if="infoDisplay">
            <div class="userMessageContent" v-for="(message, index) in infoDisplayMessage" :key="index">Chunk {{ index +
      1
              }}:<br />
              <hr><b>Page content:</b><br /> {{ message.page_content !== undefined ? message.page_content : "Empty"
              }}<br /><b>Metadata:</b><br /> {{
      message.metadata !== undefined ? message.metadata : "Empty" }}
            </div>
            <div class="close-btn" @click="closeInfo()"></div>
          </div>
          <div v-else class="messageBox">
            <template v-for="(message, index) in messages" :key="index">
              <div :class="message.from == 'user' ? 'messageFromUser' : 'messageFromChatbot'">
                <div :class="message.from == 'user' ? 'userMessageWrapper' : 'chatbotMessageWrapper'">
                  <div v-if="message.from != 'user'" class="info-button-row">
                    <button v-if="message.sourcedocs?.length > 0" class='btn btn-primary rag-info'
                      @click="openInfo(message)"></button>
                    <button class='btn btn-primary clipboard' @click="copyToClipboard(message)"></button>
                  </div>
                  <div :class="message.from == 'user' ? 'userMessageContent' : 'chatbotMessageContent'">{{ message.data
                    }}
                  </div>

                </div>
              </div>
            </template>
            <div v-if="!last_message_from_rub_chat && messages.length > 0 && loading" class="messageFromChatbot">
              <div class="chatbotMessageWrapper">
                <div class="chatbotMessageContent ellipsis-loading">
                </div>
              </div>
            </div>
          </div>
          <div :class="errorMessage.length > 0 ? 'errorbox active p-2' : 'errorbox'">
            <div :class="errorMessage.length > 0 ? 'errormessage p-2' : 'errormessage'">
              <p>{{ errorMessage }}</p>
              <button class="btn btn-primary btn-error" type="button" @click="clearError()">Ok</button>
            </div>
          </div>
          <div class="inputContainer container pb-1 mt-2">
            <div class="row">
              <div class="col-md-12">
                <div class="input-group">
                  <input type="text" class="form-control" ref="messageInput" v-model="currentMessage"
                    placeholder="Send a message" @keyup.enter="sendMessage(currentMessage)">
                  <div class="input-group-append">
                    <button class="btn btn-primary postButton" type="button" @click="sendMessage(currentMessage)"
                      aria-label="Click to send"></button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div :class="this.activetab == 'files' ? 'chat-container active' : 'chat-container'">
          <div v-if="storedfiles.length > 0" class="row">
            <div class="col-md-12">
              <h6 class="p-2">Files in this course:</h6>
              <template v-for="(file, index) in storedfiles" :key="index">
                <div class="input-group file-input-container mt-2 ">
                  <div class="file-input-item p-2">
                    <div class="file-input-name" :title="file.filename">{{ file.filename }}</div>
                  </div>
                  <div class="input-group-append">
                    <!-- Typ: {{ file.mimetype }}<br> -->
                    <button class="btn btn-primary upload-button" id="uploadButton"
                      @click="uploadFile(file.id)">&#128193
                      &#8593</button>
                  </div>
                </div>
              </template>

            </div>
          </div>
          <div v-else class="row">
            <div class="col-md-12 p-3">
              There are no files in this course, try to create a <b>file</b> or <b>folder</b> ressource.
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import * as loader from './ChatbotLoader';
import { onMounted } from 'vue';

export default {
  name: 'ChatBot',
  mounted() {
    const bm = document.querySelector('#course_file_content');
    if (bm && file_content) {
      this.storedfiles = file_content;
    }
  },
  data() {
    return {
      currentMessage: '',
      messages: [],
      succesfulUploads: [],
      loading: false,
      uploading: true,
      errorMessage: '',
      last_message_from_rub_chat: false,
      activetab: 'chat',
      infoDisplayMessage: null,
      infoDisplay: false,
      detached: false,
      interactapiurl: "Customizing/global/plugins/Services/COPage/PageComponent/ChatClient/classes/Services/interact.php",
      uploadapiurl: "Customizing/global/plugins/Services/COPage/PageComponent/ChatClient/classes/Services/upload.php",
      storedfiles: [],
    };
  },
  methods: {
    openInfo(m) {
      this.infoDisplayMessage = m.sourcedocs;
      this.infoDisplay = true;
    },
    closeInfo() {
      this.infoDisplayMessage = null;
      this.infoDisplay = false;
    },
    copyToClipboard(m) {
      navigator.clipboard.writeText(m.data);
    },
    clearError() {
      this.errorMessage = '';
    },
    async postData(url = this.interactapiurl, data = {}) {
      const formData = new URLSearchParams();
      Object.keys(data).forEach(key => {
        const value = data[key];
        formData.append(key, data[key]);
      });

      const response = await fetch(url, {
        method: "POST",
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData.toString(),
      });
      console.log(response)
      try {
        if (response.ok != true) {
          this.errorMessage = 'Error while fetching response, please try again later.';
          return "false";
        } else {
          const json = await response.json();
          return JSON.parse(json);
        }
      } catch (error) {
        console.error('Error parsing response:', error);
      }
    },
    //call php api with data
    async uploadFile(id) {
      const formData = new URLSearchParams();
      this.uploading = true;
      formData.append("filehash", id);
      formData.append("uid", "1");
      console.log(this.uploadapiurl);
      const response = await fetch(this.uploadapiurl, {
        method: "POST",
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: formData.toString(),
      });
      try {
        const text = await response.text();
        console.log(text);

        if (response.ok != true || text == "false") {
          this.errorMessage = 'Error while fetching response, please try again later.';
          this.uploading = false;
          return "false";
        } else {
          this.messages.push({
            from: 'RUB-GPT',
            data: 'Upload complete',
          });
          this.uploading = false;
        }
      } catch (error) {
        console.error('Error parsing response:', error);
        this.uploading = false;
      }
    },
    async sendMessage(message) {
      var check = this.validateMessage(message);
      if (check !== validationMsg.OK) {
        this.errorMessage = check;
        return;
      }
      this.loading = true;
      this.clearInput();

      this.messages.push({
        from: 'user',
        data: message,
      });

      let promptRequest = new Object();
      promptRequest.prompt = message;
      promptRequest.user_id = "1";
      promptRequest.course_id = "1";
      promptRequest.response = "";
      promptRequest.sourcedocs = "";

      let responseData = await this.postData(this.interactapiurl, promptRequest);
      if (responseData == "false" || responseData == null || responseData === undefined || responseData.response === undefined) {
        this.errorMessage = 'Error while fetching response, please try again later.';
      } else {
        this.messages.push({
          from: 'RUB-GPT',
          data: responseData.response,
          sourcedocs: responseData.sourcedocs
          //  sourcedocs:  [{ page_content: "page_content1", metadata: "metadata1" }, { page_content: "page_content2", metadata: "metadata2" }, { page_content: "page_content3", metadata: "m3" }, { page_content: "p4", metadata: "m4" }]
        });
      }
      this.loading = false;

    },
    validateMessage(message) {
      if (message.length == 0) {
        return validationMsg.TOOSHORT;
      }
      return validationMsg.OK;
    },
    clearInput() {
      this.currentMessage = '';
      this.$refs.messageInput.value = '';
      this.$refs.messageInput.blur();
    },
    toggleDetach() {
      // if (this.detached) {
      //   let parent = this.$refs.chatblockparent;
      //   let movable = document.getElementById('movable-chat');
      //   if (typeof (parent) === typeof (undefined)) {
      //     this.errorMessage = "Can't attach chat block. Please reload the page."
      //     return;
      //   }
      //   parent.prepend(movable);
      //   movable.style.height = `100%`;
      // } else {
      //   //Moodle content space
      //   let parent = document.querySelector(".main-inner");
      //   if (typeof (parent) === typeof (undefined)) {
      //     this.errorMessage = "Can't detach chat block."
      //     return;
      //   }
      //   let movable = document.getElementById('movable-chat');
      //   parent.prepend(movable);
      //   movable.style.height = `${parent.clientHeight}px`;
      // }
      // this.detached = !this.detached;

    }
  },
  computed: {
    pageInEdit() {
      // var editing = document.querySelector("input[id*='editingswitch']").checked;
      // if (typeof (editing) !== typeof (undefined)) {
      //   return editing;
      // }
      return true;
    },
  },
  watch: {
    messages() {
      let messageBox = document.querySelector('.messageBox');
      messageBox.scrollTop = messageBox.scrollHeight;
      let last_message = this.messages[this.messages.length - 1];
      this.last_message_from_rub_chat = last_message.from == 'RUB-GPT' ? true : false;
    }
  },
  modules: { loader }
};

class validationMsg {
  static OK = 'Ok';
  static TOOSHORT = 'Message is too short';
  static TOOLONG = 'Message too long';
  static ERROR = 'Error';
}

</script>

<style scoped>
@media only screen and (max-width: 767.98px) {

  .chatbox-chat-container .nav-tabs:not(.more-nav) .nav-item,
  .nav-pills .nav-item {
    flex: 0 0 auto;
    text-align: center;
  }


  .chatbox-chat-container #chat-tabs {
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
  }

  .chatbox-chat-container .nav-link {
    border-radius: 0.5rem;
  }

  .chatbox-chat-container-detached #chat-tabs .nav-item:nth-child(3) .nav-link {
    border-radius: 0.5rem !important;
  }
}

.chatbox-chat-container-detached {
  position: absolute;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.3);
  z-index: 2;
  display: flex;
  flex-direction: row;
  justify-content: center;
  top: 0;
  left: 0;
}

.chatbox-chat-container-detached .nav-link {
  background-color: white;
}

.dock-btn {
  cursor: pointer;
  text-align: center;
  color: white;
  border-radius: 0.5rem;
  transform: rotate(180deg);
  background-color: rgb(23, 54, 92);
  padding: 6px 12px;
}

.chatbox-chat-container-detached .chatbox-chat-container .detach-btn {
  top: 12px !important;
  right: 15px !important;
}

.chatbox-chat-container-detached .chatbox-chat-container {
  position: absolute;
  width: 50%;
  min-width: 300px;
  height: 60vh;
  min-height: 100px;
  border-top-right-radius: 0.5rem;
  border-top-left-radius: 0.5rem;
  top: 10vh;
}

.chatbox-chat-container-detached #chat-tabs .nav-item:nth-child(3) .nav-link {
  border-bottom-left-radius: 0.5rem;
  border-bottom-right-radius: 0.5rem;
  border-top-left-radius: 0rem;
  border-top-right-radius: 0rem;
}

.chatbox-chat-container-detached .detach-btn {
  transform: rotate(180deg);
}


.chatbox-chat-container {
  position: relative;

  .chat-container {
    height: 60vh;
    min-height: 100px;
    background-color: inherit;
    display: none;
    flex-direction: column;
    overflow: hidden;
    border-bottom-left-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
    background-color: white;

    input:focus {
      outline: none;
    }
  }

  #chat-tabs {
    border-top-left-radius: 0.5rem;
    border-top-right-radius: 0.5rem;
  }

  #chat-tabs .nav-item:nth-child(3) {
    margin-left: auto;
  }

  .detach-btn {
    /* border-radius: 4px;
    top: 8px;
    right: 0px;
    width: 24px;
    height: 24px; */
    background-color: #17365c;
    cursor: pointer;
    text-align: center;
    color: white;
  }


  .detach-btn:hover {
    background-color: #145CB3;
    display: block;
  }


  .chat-container.active {
    display: flex;

  }

  .messageBox {
    width: 100%;
    padding: 0 0.5rem;
    flex-grow: 1;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    /* box-shadow: 0px 10px 13px -7px #000000, 5px 5px 15px 5px rgba(0, 0, 0, 0); */
    gap: 12px;
    border-bottom: 1px solid #f0f0f0;
  }

  .info-box {
    position: relative;
    width: 100%;
    padding: 0.5rem;
    flex-grow: 1;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    /* box-shadow: 0px 10px 13px -7px #000000, 5px 5px 15px 5px rgba(0, 0, 0, 0); */
    gap: 12px;
    border-bottom: 1px solid #f0f0f0;
  }

  .info-box hr {
    margin-bottom: 5px;
    margin-top: 5px;
    border-top: 1px solid rgba(255, 255, 255, 0.5);
  }

  .info-box .userMessageContent {
    max-width: calc(100% - 25px);
  }

  .info-box .close-btn {
    /* background-color: transparent;
    color: #17365c; */
    width: 22px;
    height: 22px;
    position: absolute;
    top: 8px;
    right: 8px;
    cursor: pointer;
    display: block;
    text-align: center;
    line-height: 16px;
    border-radius: 5px;
    background-color: #17365c;
    color: white;
    border: 1px solid white;
  }

  .info-box .close-btn:after {
    content: '×';
    font-size: 20px;
  }

  .messageFromUser,
  .messageFromChatbot {
    display: flex;
  }

  .userMessageWrapper,
  .chatbotMessageWrapper {
    display: flex;
    flex-direction: row;
    flex: 1 1 100%;
    width: 100%;
  }

  .userMessageWrapper {
    justify-content: start;
  }

  .chatbotMessageWrapper {
    justify-content: end;
  }

  .userMessageContent,
  .chatbotMessageContent {
    max-width: 70%;
    padding: 8px 12px;
    border-radius: 1rem;
    margin-bottom: 2px;
    line-height: 1.4;
  }


  .chatbotMessageWrapper:hover {
    content: '';
    width: 100%;
    height: 100%;
    position: relative;
    /* background-color: rgba(0, 0, 0, 0.15); */
    display: flex;
    justify-content: flex-end;
  }

  /* .chatbotMessageContent:hover .rag-info {
    display: block;
  }
  .chatbotMessageContent:hover .clipboard {
    display: block;
  } */

  .chatbotMessageWrapper:hover .info-button-row {
    display: flex;
  }

  .chatbotMessageWrapper .info-button-row {
    display: none;
    position: absolute;
    flex-direction: row;
    justify-content: end;
    width: 30%;
    left: -3px;
    top: 2px;
  }

  .chatbotMessageWrapper .rag-info {
    display: block;
    color: white;
    position: relative;
    background-color: #17365c;
    padding: 0;
    width: 34px;
    height: 34px;
    /* width: 30px; */
    /* background-color: #17365c; */
  }

  .chatbotMessageWrapper .rag-info:before {
    content: '';
  }

  .chatbotMessageWrapper .rag-info:hover {
    background-color: white;
    color: #17365c;
    border: 1px solid #17365c;
  }

  .chatbotMessageWrapper:hover .rag-info:before {
    display: block;
    content: 'ⓘ';
    position: absolute;
    /* width: 30px;
    height: 30px; */
    font-size: 30px;
    line-height: 30px;
    top: 0;
    left: 6px;
  }

  .chatbotMessageWrapper .clipboard {
    padding: 0;
    display: block;
    color: white;
    position: relative;
    background-color: #17365c;
    width: 34px;
    height: 34px;
    margin-left: 2px;
    /* background-color: #17365c; */
  }

  .chatbotMessageWrapper .clipboard:before {
    content: '';
  }

  .chatbotMessageWrapper .clipboard:hover {
    background-color: white;
    color: #17365c;
    border: 1px solid #17365c;
  }

  .chatbotMessageWrapper:hover .clipboard:before {
    display: block;
    position: absolute;
    content: '⎘';
    font-size: 34px;
    line-height: 28px;
    top: 0;
    left: 6px;
    font-weight: bold;
    /* width: 30px;
    height: 30px; */
  }

  .userMessageContent {
    background-color: #17365c;
    color: white;
    border-top-left-radius: 0;
  }

  .chatbotMessageContent {
    border: 1px solid rgba(0, 0, 0, 0.15);
    color: #222;
    border-top-right-radius: 0;
  }

  .chatbotMessageContent.ellipsis-loading:before {
    animation: dots 2s linear infinite;
    content: '';
    height: 1rem;
    display: block;
  }

  .inputContainer {
    width: 100%;
    position: relative;
    padding-bottom: 1rem !important;
    padding-top: 0.5rem;

  }

  .postButton {
    background-color: #17365c;
    color: white;
    /* width: 3rem;
  max-width: 30px; */
    padding: 0.25rem 0.5rem;
    border: none;
    outline: none;
    cursor: pointer;
    /* border-radius: 0.5rem; */
    transition: background-color 0.3s ease-in-out;
    /* font-size: 1.25rem; */
    height: 100%;
  }

  .postButton:before {
    content: '\27A5';
  }

  .postButton:hover {
    background-color: #145CB3;
  }

  .upload-button {
    background-color: #17365c;
  }

  .upload-button:hover {
    background-color: #145CB3;
  }

  /* @media (max-width: 480px) {
    .chat-container {
      width: 100%;
      height: 750px;
      max-width: none;
      border-radius: 0;
    }
  } */

  .messageBox {
    padding: 0.5rem;
    flex-grow: 1;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .messageFromUser,
  .messageFromChatbot {
    display: flex;
  }

  .chat-loader {
    position: absolute;
    top: calc(50% - 50px);
    left: calc(50% - 50px);
  }


  .errorbox {
    height: 0rem;
    position: absolute;
    border-bottom-left-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
    /* bottom: 0; */
    left: 0;
    z-index: 4;
    background-color: #fff;
    color: #fff;
    /* transition: all 0.5s ease; */
    display: flex;
    flex-direction: row;

    .errormessage {
      height: 0;
      background-color: #17365c;
      color: white;
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: center;
      border-radius: 0.5rem;
      ;
    }

    .btn-error {
      display: none;
      font-size: 1rem;
      color: white
    }
  }

  .errorbox.active {
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    width: 100%;
    background-color: rgba(0.137, 0.329, 0.573, 0.6);

    .errormessage {
      height: auto;
    }

    .btn-error {
      display: block;
    }
  }

  .file-input-container {
    position: relative;
    width: 100%;
    flex-wrap: nowrap;
  }

  .file-input-item {
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    width: calc(100% - 62px);
  }

  .file-input-name {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  /* Hide the default file input */
  .file-input {
    position: absolute;
    top: 0;
    left: 0;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
  }

  /* Style the custom file label */
  .custom-label-file {
    background-color: #f0f0f0;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .custom-label-file:after {
    content: '';
  }

}

@keyframes dots {

  0%,
  20% {
    content: '.'
  }

  40% {
    content: '..'
  }

  60% {
    content: '...'
  }

  90%,
  100% {
    content: ''
  }
}
</style>