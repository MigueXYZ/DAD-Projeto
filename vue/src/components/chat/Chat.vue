<script setup>
import { ref, inject } from 'vue'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card/index.js'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

import { useChatStore } from '@/stores/chat.js'
import { useAuthStore } from '@/stores/auth.js'

const storeChat = useChatStore()
const storeAuth = useAuthStore()

const inputDialog = inject('inputDialog')

const message = ref('')

const canSendMessageToUser = (user) => {
  return user && storeAuth.user && user.id !== storeAuth.user.id
}

const sendMessageToChat = () => {
  storeChat.sendMessageToChat(message.value)
  message.value = ''
}

let userDestination = null
const sendPrivateMessageToUser = (user) => {
  userDestination = null
  if (canSendMessageToUser(user)) {
    userDestination = user
    inputDialog.value.open(
        handleMessageFromInputDialog,
        'Message to ' + user.name,
        `Only ${user.name} will receive this message!`,
        'Message', '',
        'Close', 'Send',
        ''
    )
  }
}

const handleMessageFromInputDialog = (message) => {
  storeChat.sendPrivateMessageToUser(userDestination, message)
}
</script>

<template>
  <Card class="my-8 py-2 px-1">
    <CardHeader class="pb-6">
      <CardTitle>Chat</CardTitle>
      <CardDescription>
        Only the latest 10 messages.<br>
        <em>Click on the user name to send him a private message.</em>
      </CardDescription>
      <Label for="inputMessage" class="pt-4">
        Press enter to send message:
      </Label>
      <Input id="inputMessage" v-model="message" @keydown.enter="sendMessageToChat"/>
    </CardHeader>
    <CardContent class="p-4">
      <div class="divide-y divide-solid divide-gray-200">
        <div v-if="storeChat.totalMessages > 0">
          <div v-for="messageObj in storeChat.messages" :key="messageObj" class="flex">
            <div class="flex flex-col grow pb-6">
              <div class="text-xs text-gray-500">
                                <span :class="{
                                    'hover:text-green-300': canSendMessageToUser(messageObj.user),
                                    'hover:cursor-pointer' :canSendMessageToUser(messageObj.user)
                                }" @click="sendPrivateMessageToUser(messageObj.user)">{{ messageObj.user?.name ?? 'Anonymous' }}</span>
              </div>
              <div class="mt-1 text-base grow leading-6">
                {{ messageObj.message }}
              </div>
            </div>
          </div>
        </div>
        <div v-else>
          <h2 class="text-xl">No messages!</h2>
        </div>
      </div>
    </CardContent>
  </Card>
</template>