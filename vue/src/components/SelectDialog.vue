<script setup>
import { ref } from 'vue'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Label } from '@/components/ui/label'

const isOpen = ref(false)
const titleText = ref('Title')
const descriptionText = ref('')
const mainContentText = ref('')
const cancelText = ref('Cancel')
const actionText = ref('OK')
const actionCallBack = ref(null)
const selectedOption = ref('')
const optionsList = ref([])  // Array for the available options

const open = (actionCallBackFunction,
              title = 'Title',
              description = '',
              options = [],
              actionLabel = 'Save',
              mainText = ''
) => {
  titleText.value = title
  descriptionText.value = description
  optionsList.value = options  // Setting the list of options
  actionText.value = actionLabel
  actionCallBack.value = actionCallBackFunction
  mainContentText.value = mainText
  isOpen.value = true;
}

const cancel = () => {
  isOpen.value = false
}

// Only executes handle Action if a valid option is selected
const handleAction = () => {
  if (actionCallBack.value) {
    if (selectedOption.value) {
      actionCallBack.value(selectedOption.value)  // Call the action callback with the selected option
      isOpen.value = false  // Close the dialog after the action
    }
  }
}

defineExpose({
  open
})

</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogContent class="sm:max-w-[600px]">
      <DialogHeader>
        <DialogTitle>{{ titleText }}</DialogTitle>
        <DialogDescription>
          {{ descriptionText }}
        </DialogDescription>
      </DialogHeader>
      <div>
        <div class="text-base pb-5">
          {{ mainContentText }}
        </div>
        <div class="flex-col space-y-2">
          <Label for="selectField">
            Select a Game
          </Label>
          <select
              id="selectField"
              v-model="selectedOption"
              class="border border-gray-300 p-2 rounded-md"
          >
            <option value="" disabled>Select...</option>
            <option v-for="option in optionsList" :key="option.id" :value="option">
              {{ option.board_cols }}x{{ option.board_rows }}
            </option>
          </select>
        </div>
      </div>
      <DialogFooter class="flex justify-end space-x-3">
        <Button @click="cancel" class="px-8">
          Cancel
        </Button>
        <Button @click="handleAction" :disabled="!selectedOption" class="px-8">
          {{ actionText }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
